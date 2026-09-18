<?php

namespace LaraGram\Laraquest;

use LaraGram\Laraquest\Connection\Curl;
use LaraGram\Laraquest\Connection\NoResponseCurl;
use LaraGram\Laraquest\Exceptions\TelegramApiException;

trait Methode
{
    use APIMethods;

    private ?string $perCallConnection = null;
    private ?int $perCallMode = null;
    private ?bool $perCallThrow = null;

    private ?array $resolvedConfig = null;

    /**
     * Whether failed calls throw by default, when nothing else decides.
     *
     * @var bool|null
     */
    private static ?bool $throwByDefault = null;

    public static function setDefaultConnection(string $name): void
    {
        ConnectionRegistry::setDefaultConnection($name);
    }

    public static function getDefaultConnection(): ?string
    {
        return ConnectionRegistry::getDefaultConnection();
    }

    public static function setDefaultMode(Mode|int $mode): void
    {
        ConnectionRegistry::setDefaultMode($mode);
    }

    public static function getDefaultMode(): int
    {
        return ConnectionRegistry::getDefaultMode();
    }

    public function connection(string $name): static
    {
        $this->perCallConnection = $name;
        return $this;
    }

    public function mode(Mode|int $mode): static
    {
        $this->perCallMode = $mode instanceof Mode ? $mode->value : $mode;
        return $this;
    }

    /**
     * Turn a failed response of the next call into an exception.
     *
     * The exception is the one that matches the failure, so a caller may catch
     * exactly what it knows how to handle.
     */
    public function throw(bool $throw = true): static
    {
        $this->perCallThrow = $throw;
        return $this;
    }

    /**
     * Let the next call return its failed response instead of throwing.
     */
    public function silent(): static
    {
        $this->perCallThrow = false;
        return $this;
    }

    /**
     * Make every failed call throw, unless it asks not to with silent().
     */
    public static function throwOnErrors(bool $throw = true): void
    {
        self::$throwByDefault = $throw;
    }

    /**
     * Stop failed calls from throwing by default.
     */
    public static function returnErrors(): void
    {
        self::$throwByDefault = false;
    }

    public function getConnection(): string
    {
        return $this->resolveConnection();
    }

    private function resolveConfig(): array
    {
        if ($this->resolvedConfig !== null) {
            return $this->resolvedConfig;
        }

        if (class_exists(\LaraGram\Config\Repository::class)) {
            $this->resolvedConfig = [
                'default_mode' => config('laraquest.default_mode'),
                'api_server' => config('bot.api_server.endpoint'),
                'tokens' => config('bot.connections'),
                'default_con' => config('bot.default'),
                'default_parameters' => config('laraquest.default_parameters') ?? [],
                'throw_exceptions' => config('laraquest.throw_exceptions') ?? false,
            ];
        } else {
            $this->resolvedConfig = [
                'default_mode' => $_ENV['DEFAULT_MODE'],
                'api_server' => $_ENV['BOT_API_SERVER'],
                'tokens' => $_ENV['CONNECTIONS'] ?? [],
                'default_con' => null,
                'default_parameters' => $_ENV['DEFAULT_PARAMETERS'] ?? [],
                'throw_exceptions' => filter_var($_ENV['THROW_EXCEPTIONS'] ?? false, FILTER_VALIDATE_BOOL),
            ];
        }

        return $this->resolvedConfig;
    }

    private function resolveConnection(): string
    {
        $perCall = $this->perCallConnection !== 'auto'
            ? $this->perCallConnection
            : null;

        $connection = $perCall
            ?? ConnectionRegistry::getDefaultConnection()
            ?? $this->resolveConfig()['default_con']
            ?? throw new \RuntimeException("No connection configured.");

        if ($connection === 'auto') {
            throw new \RuntimeException(
                "Connection is 'auto' but setDefaultConnection() has not been called yet."
            );
        }

        return $connection;
    }

    private function resolveToken(string $connection): string
    {
        $tokens = $this->resolveConfig()['tokens'];

        if (class_exists(\LaraGram\Config\Repository::class)) {
            return $tokens[$connection]['token']
                ?? throw new \RuntimeException("No token for connection: {$connection}");
        }

        return $tokens[$connection]['BOT_TOKEN']
            ?? $_ENV['BOT_TOKEN']
            ?? throw new \RuntimeException("No token for connection: {$connection}");
    }

    private function resolveMode(): int
    {
        $mode = $this->perCallMode ?? ConnectionRegistry::getDefaultMode();

        if ($mode !== 0) {
            return $mode;
        }

        return match ($this->resolveConfig()['default_mode']) {
            'no_response_curl' => Mode::NO_RESPONSE_CURL->value,
            default => Mode::CURL->value,
        };
    }

    private function applyDefaultParameters(string $method, array $params): array
    {
        $config = $this->resolveConfig()['default_parameters'] ?? [];
        if (!$config) {
            return $params;
        }

        $defaults = [];

        foreach ($config['groups'] ?? [] as $group) {
            if (in_array($method, $group['methods'] ?? [], true)) {
                $defaults = array_merge($defaults, $group['defaults'] ?? []);
            }
        }

        if (isset($config[$method]) && is_array($config[$method])) {
            $defaults = array_merge($defaults, $config[$method]);
        }

        foreach ($defaults as $key => $value) {
            if (array_key_exists($key, $params) && $params[$key] === null) {
                $params[$key] = $value;
            }
        }

        return $params;
    }

    /**
     * Call a Bot API method and get its response.
     *
     * @param  string  $method
     * @param  array<string, mixed>  $params
     * @return \LaraGram\Laraquest\Response
     *
     * @throws \LaraGram\Laraquest\Exceptions\TelegramApiException
     */
    private function endpoint(string $method, array $params): Response
    {
        $connection = $this->resolveConnection();
        $mode = $this->resolveMode();
        $throw = $this->resolveThrow();

        // reset per-call overrides
        $this->perCallConnection = null;
        $this->perCallMode = null;
        $this->perCallThrow = null;

        $params = $this->applyDefaultParameters($method, $params);
        $params = array_filter($params, fn($v) => $v !== null);

        foreach ($params as $key => $value) {
            if ($value instanceof \CURLFile || $value instanceof \CURLStringFile) {
                continue;
            }

            if (is_object($value) || is_array($value)) {
                $params[$key] = json_encode($value);
            }
        }

        $token = $this->resolveToken($connection);
        $apiServer = $this->resolveConfig()['api_server'];

        $response = match ($mode) {
            Mode::NO_RESPONSE_CURL->value => (new NoResponseCurl($token, $apiServer))->endpoint($method, $params),
            default => (new Curl($token, $apiServer))->endpoint($method, $params),
        };

        if ($throw && ($response['ok'] ?? true) === false) {
            throw TelegramApiException::create($method, $params, $response);
        }

        return Response::make($response, $method, $params);
    }

    /**
     * Determine whether a failed response should be thrown.
     */
    private function resolveThrow(): bool
    {
        if ($this->perCallThrow !== null) {
            return $this->perCallThrow;
        }

        if (self::$throwByDefault !== null) {
            return self::$throwByDefault;
        }

        return (bool) ($this->resolveConfig()['throw_exceptions'] ?? false);
    }
}
