<?php

namespace LaraGram\Laraquest;

use LaraGram\Laraquest\Connection\Curl;
use LaraGram\Laraquest\Connection\NoResponseCurl;

trait Methode
{
    use APIMethods;

    private ?string $perCallConnection = null;
    private ?int $perCallMode = null;

    private ?array $resolvedConfig = null;

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
                'update_type' => config('laraquest.update_type'),
                'api_server' => config('bot.api_server.endpoint'),
                'tokens' => config('bot.connections'),
                'default_con' => config('bot.default'),
            ];
        } else {
            $this->resolvedConfig = [
                'update_type' => $_ENV['UPDATE_TYPE'],
                'api_server' => $_ENV['BOT_API_SERVER'],
                'tokens' => $_ENV['CONNECTIONS'] ?? [],
                'default_con' => null,
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

        return match ($this->resolveConfig()['update_type']) {
            'no_response_curl' => Mode::NO_RESPONSE_CURL->value,
            default => Mode::CURL->value,
        };
    }

    private function endpoint(string $method, array $params): mixed
    {
        $connection = $this->resolveConnection();
        $mode = $this->resolveMode();

        // reset per-call overrides
        $this->perCallConnection = null;
        $this->perCallMode = null;

        $params = array_filter($params, fn($v) => $v !== null);

        foreach ($params as $key => $value) {
            if (is_object($value)) {
                $params[$key] = json_encode($value);
            }
        }

        $token = $this->resolveToken($connection);
        $apiServer = $this->resolveConfig()['api_server'];

        return match ($mode) {
            Mode::NO_RESPONSE_CURL->value => (new NoResponseCurl($token, $apiServer))->endpoint($method, $params),
            default => (new Curl($token, $apiServer))->endpoint($method, $params),
        };
    }
}
