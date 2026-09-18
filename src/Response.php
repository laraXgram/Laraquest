<?php

namespace LaraGram\Laraquest;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use LaraGram\Laraquest\Exceptions\TelegramApiException;
use LaraGram\Laraquest\Schema\ApiSchema;
use LaraGram\Laraquest\Support\UpdateObject;
use Stringable;
use Traversable;

class Response implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable, Stringable
{
    /**
     * The keys of the envelope Telegram wraps every result in.
     *
     * @var array<int, string>
     */
    private const ENVELOPE = ['ok', 'result', 'error_code', 'description', 'parameters'];

    /**
     * The result, once it has been turned into the object it describes.
     *
     * @var mixed
     */
    private mixed $object = null;

    /**
     * Whether the result has already been turned into an object.
     *
     * @var bool
     */
    private bool $resolved = false;

    /**
     * @param  string  $method  The Bot API method that was called.
     * @param  array<string, mixed>  $payload  The response Telegram sent back.
     * @param  array<string, mixed>  $parameters  The parameters the method was called with.
     */
    final public function __construct(
        private readonly string $method = '',
        private readonly array $payload = [],
        private readonly array $parameters = [],
    ) {
    }

    /**
     * Wrap a raw response, leaving one that is already wrapped alone.
     *
     * @param  mixed  $payload
     * @param  string  $method
     * @param  array<string, mixed>  $parameters
     * @return static
     */
    public static function make(mixed $payload, string $method = '', array $parameters = []): static
    {
        if ($payload instanceof self) {
            return $payload;
        }

        if ($payload instanceof UpdateObject) {
            $payload = ['ok' => true, 'result' => $payload->toArray()];
        }

        if (is_string($payload)) {
            $decoded = json_decode($payload, true);
            $payload = is_array($decoded) ? $decoded : ['ok' => true, 'result' => $payload];
        }

        if (is_object($payload)) {
            $payload = (array) json_decode(json_encode($payload), true);
        }

        if (! is_array($payload)) {
            $payload = ['ok' => true, 'result' => $payload === null ? true : $payload];
        }

        return new static($method, $payload, $parameters);
    }

    /**
     * Determine whether Telegram accepted the call.
     *
     * @return bool
     */
    public function isOk(): bool
    {
        return (bool) ($this->payload['ok'] ?? false);
    }

    /**
     * Determine whether Telegram refused the call.
     *
     * @return bool
     */
    public function failed(): bool
    {
        return ! $this->isOk();
    }

    /**
     * Get the result of the call, as the object it describes.
     *
     * Pass true to get it exactly as Telegram sent it.
     *
     * @param  bool  $raw
     * @return mixed
     */
    public function result(bool $raw = false): mixed
    {
        if ($raw) {
            return $this->payload['result'] ?? null;
        }

        if ($this->resolved) {
            return $this->object;
        }

        $this->resolved = true;

        return $this->object = $this->hydrate($this->payload['result'] ?? null);
    }

    /**
     * Get the error code Telegram sent back, if the call failed.
     *
     * @return int|null
     */
    public function errorCode(): ?int
    {
        return isset($this->payload['error_code']) ? (int) $this->payload['error_code'] : null;
    }

    /**
     * Get the description Telegram sent back, if the call failed.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return isset($this->payload['description']) ? (string) $this->payload['description'] : null;
    }

    /**
     * Get the extra parameters Telegram attached to a failure, such as the
     * seconds to wait or the chat the conversation moved to.
     *
     * @return array<string, mixed>
     */
    public function parameters(): array
    {
        return (array) ($this->payload['parameters'] ?? []);
    }

    /**
     * Get the number of seconds to wait before retrying, when Telegram said so.
     *
     * @return int|null
     */
    public function retryAfter(): ?int
    {
        $retryAfter = $this->parameters()['retry_after'] ?? null;

        return $retryAfter === null ? null : (int) $retryAfter;
    }

    /**
     * Get the chat the conversation moved to, when Telegram said so.
     *
     * @return int|null
     */
    public function migrateToChatId(): ?int
    {
        $chatId = $this->parameters()['migrate_to_chat_id'] ?? null;

        return $chatId === null ? null : (int) $chatId;
    }

    /**
     * Get the Bot API method that produced this response.
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
    public function sent(): array
    {
        return $this->parameters;
    }

    /**
     * Throw the exception matching a failed call, and do nothing otherwise.
     *
     * @return $this
     *
     * @throws \LaraGram\Laraquest\Exceptions\TelegramApiException
     */
    public function throw(): static
    {
        if ($this->failed()) {
            throw TelegramApiException::create($this->method, $this->parameters, $this->payload);
        }

        return $this;
    }

    /**
     * Get the result as an array, or the whole response when asked for it.
     *
     * @param  bool  $full
     * @return array<string|int, mixed>
     */
    public function toArray(bool $full = false): array
    {
        if ($full) {
            return $this->payload;
        }

        $result = $this->payload['result'] ?? null;

        return match (true) {
            is_array($result) => $result,
            $result === null => [],
            default => ['result' => $result],
        };
    }

    /**
     * Get the result as JSON, or the whole response when asked for it.
     *
     * @param  bool  $full
     * @param  int  $flags
     * @return string
     */
    public function toJson(bool $full = false, int $flags = 0): string
    {
        return json_encode(
            $full ? $this->payload : ($this->payload['result'] ?? null),
            $flags | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Read a field of the result, falling back to the envelope around it.
     *
     * @param  string  $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        $result = $this->result();

        if ($result instanceof UpdateObject && isset($result->{$name})) {
            return $result->{$name};
        }

        if (is_array($result) && array_key_exists($name, $result)) {
            return $result[$name];
        }

        if ($name === 'result') {
            return $result;
        }

        return in_array($name, self::ENVELOPE, true) ? ($this->payload[$name] ?? null) : null;
    }

    /**
     * @param  string  $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return $this->__get($name) !== null;
    }

    /**
     * Forward an unknown call to the object the result describes.
     *
     * @param  string  $method
     * @param  array<int, mixed>  $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments): mixed
    {
        $result = $this->result();

        if ($result instanceof UpdateObject && method_exists($result, $method)) {
            return $result->{$method}(...$arguments);
        }

        throw new \BadMethodCallException(
            'Method ['.$method.'] does not exist on the response of ['.$this->method.'].'
        );
    }

    /**
     * @param  mixed  $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->payload[$offset]);
    }

    /**
     * Read the response exactly as the array Telegram sent.
     *
     * @param  mixed  $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->payload[$offset] ?? null;
    }

    /**
     * @param  mixed  $offset
     * @param  mixed  $value
     * @return void
     *
     * @throws \LogicException
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \LogicException('A Bot API response may not be changed.');
    }

    /**
     * @param  mixed  $offset
     * @return void
     *
     * @throws \LogicException
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new \LogicException('A Bot API response may not be changed.');
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->payload);
    }

    /**
     * @return \Traversable<string, mixed>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->payload);
    }

    /**
     * Encode the whole response, exactly as Telegram sent it.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->payload;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJson(true);
    }

    /**
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return $this->payload;
    }

    /**
     * Turn the result into the object, or list of objects, the method returns.
     *
     * @param  mixed  $result
     * @return mixed
     */
    private function hydrate(mixed $result): mixed
    {
        if (! is_array($result) || $this->method === '') {
            return $result;
        }

        $returns = ApiSchema::returns($this->method);

        if ($returns === null) {
            return $result;
        }

        if (preg_match('/^Array of (.+)$/i', $returns, $matches)) {
            $class = $this->classFor(trim($matches[1]));

            return $class === null ? $result : $class::collect($result);
        }

        $class = $this->classFor($returns);

        return $class === null ? $result : $class::from($result);
    }

    /**
     * Resolve the update object class of a documented return type.
     *
     * @param  string  $type
     * @return class-string<\LaraGram\Laraquest\Support\UpdateObject>|null
     */
    private function classFor(string $type): ?string
    {
        if (str_contains($type, '|')) {
            $type = trim(explode('|', $type)[0]);
        }

        $class = __NAMESPACE__.'\\Updates\\'.$type;

        return class_exists($class) && is_subclass_of($class, UpdateObject::class) ? $class : null;
    }
}
