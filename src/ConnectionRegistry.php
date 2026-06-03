<?php

namespace LaraGram\Laraquest;

class ConnectionRegistry
{
    private static ?string $defaultConnection = null;
    private static int $defaultMode = 0;

    public static function setDefaultConnection(string $name): void
    {
        self::$defaultConnection = $name;
    }

    public static function getDefaultConnection(): ?string
    {
        return self::$defaultConnection;
    }

    public static function setDefaultMode(Mode|int $mode): void
    {
        self::$defaultMode = $mode instanceof Mode ? $mode->value : $mode;
    }

    public static function getDefaultMode(): int
    {
        return self::$defaultMode;
    }
}
