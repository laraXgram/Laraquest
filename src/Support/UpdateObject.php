<?php

namespace LaraGram\Laraquest\Support;

abstract class UpdateObject implements \JsonSerializable
{
    private array $__fields;

    final public function __construct(mixed ...$fields)
    {
        $this->__fields = $fields;
    }

    public static function init(mixed ...$fields): static
    {
        return new static(...$fields);
    }

    public function __get(string $name): mixed
    {
        return $this->__fields[$name] ?? null;
    }

    public function __isset(string $name): bool
    {
        return isset($this->__fields[$name]);
    }

    public function __set(string $name, mixed $value): void
    {
        $this->__fields[$name] = $value;
    }

    public function toArray(): array
    {
        return array_map([self::class, 'normalize'], $this->__fields);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    private static function normalize(mixed $value): mixed
    {
        if ($value instanceof self) {
            return $value->toArray();
        }

        if (is_array($value)) {
            return array_map([self::class, 'normalize'], $value);
        }

        return $value;
    }
}
