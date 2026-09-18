<?php

namespace LaraGram\Laraquest\Exceptions;

use RuntimeException;

/**
 * A Bot API call that came back with "ok": false.
 *
 * The concrete subclass is picked from the error code and description, so a
 * caller may catch exactly the failure it knows how to handle - a blocked bot,
 * a chat that moved, a flood wait - and let the rest bubble up.
 */
class TelegramApiException extends RuntimeException
{
    /**
     * The Bot API method that failed.
     *
     * @var string
     */
    protected string $method = '';

    /**
     * The parameters the method was called with.
     *
     * @var array<string, mixed>
     */
    protected array $parameters = [];

    /**
     * The response Telegram sent back.
     *
     * @var array<string, mixed>
     */
    protected array $response = [];

    /**
     * The description patterns each exception answers to, most specific first.
     *
     * @var array<class-string<self>, array<int, string>>
     */
    protected const DESCRIPTIONS = [
        MessageNotModifiedException::class => ['message is not modified'],
        MessageNotFoundException::class => [
            'message to edit not found', 'message to delete not found', 'message to forward not found',
            'message to copy not found', 'message to pin not found', 'message to reply not found',
            'message identifier is not specified', "message can't be edited",
        ],
        ChatMigratedException::class => ['group chat was upgraded to a supergroup chat', 'migrate to chat id'],
        ChatNotFoundException::class => ['chat not found', 'chat_id is empty', 'peer_id_invalid'],
        UserNotFoundException::class => ['user not found', 'user_id_invalid'],
        BotBlockedException::class => ['bot was blocked by the user', 'user is blocked'],
        UserDeactivatedException::class => ['user is deactivated'],
        BotKickedException::class => ['bot was kicked', 'bot is not a member', "bot can't initiate conversation"],
        NotEnoughRightsException::class => [
            'not enough rights', 'have no rights', "can't remove chat owner",
            'need administrator rights', 'method is available for supergroup',
        ],
        InvalidFileException::class => [
            'wrong file identifier', 'wrong remote file identifier', 'failed to get http url content',
            'wrong file id', 'file is too big', 'wrong type of the web page content',
        ],
        InvalidTokenException::class => ['bot token is invalid', 'unauthorized'],
        FloodException::class => ['too many requests', 'flood control exceeded', 'retry after'],
    ];

    /**
     * The exception each HTTP-like error code answers to.
     *
     * @var array<int, class-string<self>>
     */
    protected const CODES = [
        400 => BadRequestException::class,
        401 => UnauthorizedException::class,
        403 => ForbiddenException::class,
        404 => NotFoundException::class,
        409 => ConflictException::class,
        413 => RequestEntityTooLargeException::class,
        429 => FloodException::class,
        500 => InternalServerErrorException::class,
        502 => InternalServerErrorException::class,
        503 => InternalServerErrorException::class,
    ];

    /**
     * Build the exception matching a failed response.
     *
     * @param  string  $method
     * @param  array<string, mixed>  $parameters
     * @param  array<string, mixed>  $response
     * @return static
     */
    public static function create(string $method, array $parameters, array $response): self
    {
        $code = (int) ($response['error_code'] ?? 0);
        $description = (string) ($response['description'] ?? $response['message'] ?? 'Unknown Telegram error.');

        $class = ! empty($response['network'])
            ? ConnectionException::class
            : static::classFor($code, $description);

        $exception = new $class("[{$method}] {$description}", $code);

        $exception->method = $method;
        $exception->parameters = $parameters;
        $exception->response = $response;

        return $exception;
    }

    /**
     * Resolve the exception class for an error code and description.
     *
     * @param  int  $code
     * @param  string  $description
     * @return class-string<self>
     */
    protected static function classFor(int $code, string $description): string
    {
        $needle = strtolower($description);

        foreach (static::DESCRIPTIONS as $class => $patterns) {
            foreach ($patterns as $pattern) {
                if (str_contains($needle, $pattern)) {
                    return $class;
                }
            }
        }

        if ($code >= 500) {
            return InternalServerErrorException::class;
        }

        return static::CODES[$code] ?? self::class;
    }

    /**
     * Get the Bot API method that failed.
     *
     * @return string
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * Get the parameters the method was called with.
     *
     * @return array<string, mixed>
     */
    public function parameters(): array
    {
        return $this->parameters;
    }

    /**
     * Get the whole response Telegram sent back.
     *
     * @return array<string, mixed>
     */
    public function response(): array
    {
        return $this->response;
    }

    /**
     * Get the error code Telegram sent back.
     *
     * @return int
     */
    public function errorCode(): int
    {
        return (int) ($this->response['error_code'] ?? $this->getCode());
    }

    /**
     * Get the description Telegram sent back.
     *
     * @return string
     */
    public function description(): string
    {
        return (string) ($this->response['description'] ?? $this->getMessage());
    }

    /**
     * Get the "parameters" Telegram attached to the error, such as the number
     * of seconds to wait or the chat the conversation moved to.
     *
     * @return array<string, mixed>
     */
    public function responseParameters(): array
    {
        return (array) ($this->response['parameters'] ?? []);
    }

    /**
     * Get the number of seconds to wait before retrying, when Telegram said so.
     *
     * @return int|null
     */
    public function retryAfter(): ?int
    {
        $retryAfter = $this->responseParameters()['retry_after'] ?? null;

        if ($retryAfter === null && preg_match('/retry after (\d+)/i', $this->description(), $matches)) {
            $retryAfter = $matches[1];
        }

        return $retryAfter === null ? null : (int) $retryAfter;
    }

    /**
     * Get the chat the conversation moved to, when Telegram said so.
     *
     * @return int|null
     */
    public function migrateToChatId(): ?int
    {
        $chatId = $this->responseParameters()['migrate_to_chat_id'] ?? null;

        return $chatId === null ? null : (int) $chatId;
    }

    /**
     * Determine whether retrying the same call could succeed.
     *
     * @return bool
     */
    public function isRetryable(): bool
    {
        return $this instanceof FloodException
            || $this instanceof InternalServerErrorException
            || $this instanceof ConnectionException;
    }
}
