<?php

namespace LaraGram\Laraquest\Schema;

use RuntimeException;

class ApiSchema
{
    private static ?array $schema = null;

    /**
     * Get the path of the generated schema file.
     */
    public static function path(): string
    {
        return __DIR__ . '/api.php';
    }

    /**
     * Determine if the schema has been generated.
     */
    public static function exists(): bool
    {
        return is_file(static::path());
    }

    /**
     * Get the whole schema: version, scraped_at, methods and types.
     */
    public static function all(): array
    {
        if (self::$schema !== null) {
            return self::$schema;
        }

        if (!static::exists()) {
            throw new RuntimeException('The Telegram API schema has not been generated. Run [vendor/bin/parse-telegram-api.php].');
        }

        return self::$schema = require static::path();
    }

    /**
     * Get the Bot API version the schema was generated from.
     */
    public static function version(): ?string
    {
        return static::all()['version'];
    }

    /**
     * Get all methods keyed by name.
     *
     * @return array<string, array{description: string, returns: string|null, parameters: array<int, array{name: string, type: string, required: bool, description: string}>}>
     */
    public static function methods(): array
    {
        return static::all()['methods'];
    }

    /**
     * Get a single method, or null if it doesn't exist.
     */
    public static function method(string $name): ?array
    {
        return static::methods()[$name] ?? null;
    }

    /**
     * Get all types keyed by name.
     *
     * @return array<string, array{description: string, fields: array<int, array{name: string, type: string, required: bool, description: string}>}>
     */
    public static function types(): array
    {
        return static::all()['types'];
    }

    /**
     * Get a single type, or null if it doesn't exist.
     */
    public static function type(string $name): ?array
    {
        return static::types()[$name] ?? null;
    }
}
