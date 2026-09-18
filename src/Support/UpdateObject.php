<?php

namespace LaraGram\Laraquest\Support;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

abstract class UpdateObject implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    /**
     * The type of each field that is an object of its own.
     *
     * A generated subclass fills this in; an array value means a list of that
     * type. It is what lets `$message->chat->id` work on a raw payload.
     *
     * @var array<string, class-string<self>|array<int, class-string<self>>>
     */
    protected const FIELDS = [];

    /**
     * The fields this object carries.
     *
     * @var array<string, mixed>
     */
    private array $__fields;

    /**
     * The fields that have already been turned into objects.
     *
     * @var array<string, mixed>
     */
    private array $__hydrated = [];

    /**
     * @param  mixed  ...$fields
     */
    final public function __construct(mixed ...$fields)
    {
        $this->__fields = $fields;
    }

    /**
     * Build the object from an array of fields.
     *
     * @param  array<string, mixed>  $fields
     * @return static
     */
    public static function make(array $fields): static
    {
        return new static(...array_filter($fields, static fn ($value) => $value !== null));
    }

    /**
     * Build the object from anything Telegram may hand over: an array, a
     * decoded object, a JSON string, or another update object.
     *
     * @param  mixed  $data
     * @return static
     */
    public static function from(mixed $data): static
    {
        if ($data instanceof static) {
            return $data;
        }

        if ($data instanceof self) {
            return static::make($data->fields());
        }

        if (is_string($data)) {
            $data = json_decode($data, true) ?? [];
        }

        if (is_object($data)) {
            $data = json_decode(json_encode($data), true) ?? [];
        }

        return static::make(is_array($data) ? $data : []);
    }

    /**
     * Build a list of objects from a list of payloads.
     *
     * @param  iterable<mixed>  $items
     * @return array<int, static>
     */
    public static function collect(iterable $items): array
    {
        $objects = [];

        foreach ($items as $item) {
            $objects[] = static::from($item);
        }

        return $objects;
    }

    /**
     * Get a field, as an object when the field is one.
     *
     * @param  string  $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        if (array_key_exists($name, $this->__hydrated)) {
            return $this->__hydrated[$name];
        }

        $value = $this->__fields[$name] ?? null;

        if ($value === null || ! isset(static::FIELDS[$name])) {
            return $value;
        }

        return $this->__hydrated[$name] = $this->hydrate(static::FIELDS[$name], $value);
    }

    /**
     * @param  string  $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($this->__fields[$name]);
    }

    /**
     * @param  string  $name
     * @param  mixed  $value
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        $this->__fields[$name] = $value;

        unset($this->__hydrated[$name]);
    }

    /**
     * @param  string  $name
     * @return void
     */
    public function __unset(string $name): void
    {
        unset($this->__fields[$name], $this->__hydrated[$name]);
    }

    /**
     * Get a field, reaching into nested objects with a dotted path.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this;

        foreach (explode('.', $key) as $segment) {
            $value = match (true) {
                $value instanceof self => $value->{$segment},
                is_array($value) => $value[$segment] ?? null,
                is_object($value) => $value->{$segment} ?? null,
                default => null,
            };

            if ($value === null) {
                return $default;
            }
        }

        return $value;
    }

    /**
     * Determine whether a field, or a dotted path, carries a value.
     *
     * @param  string  $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Determine whether the object carries no fields at all.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->__fields === [];
    }

    /**
     * Get the raw fields, exactly as they were given.
     *
     * @return array<string, mixed>
     */
    public function fields(): array
    {
        return $this->__fields;
    }

    /**
     * Get the raw value of a field, without turning it into an object.
     *
     * @param  string  $name
     * @param  mixed  $default
     * @return mixed
     */
    public function raw(string $name, mixed $default = null): mixed
    {
        return $this->__fields[$name] ?? $default;
    }

    /**
     * Get a copy carrying only the given fields.
     *
     * @param  array<int, string>  $keys
     * @return static
     */
    public function only(array $keys): static
    {
        return static::make(array_intersect_key($this->__fields, array_flip($keys)));
    }

    /**
     * Get a copy without the given fields.
     *
     * @param  array<int, string>  $keys
     * @return static
     */
    public function except(array $keys): static
    {
        return static::make(array_diff_key($this->__fields, array_flip($keys)));
    }

    /**
     * Get a copy with the given fields added or replaced.
     *
     * @param  array<string, mixed>  $fields
     * @return static
     */
    public function merge(array $fields): static
    {
        return static::make(array_merge($this->__fields, $fields));
    }

    /**
     * Get the object as a plain array, ready to be sent to Telegram.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_map([self::class, 'normalize'], $this->__fields);
    }

    /**
     * Get the object as the JSON Telegram expects.
     *
     * @param  int  $flags
     * @return string
     */
    public function toJson(int $flags = 0): string
    {
        return json_encode($this->jsonSerialize(), $flags | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @param  mixed  $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->__fields[$offset]);
    }

    /**
     * @param  mixed  $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->__get((string) $offset);
    }

    /**
     * @param  mixed  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->__set((string) $offset, $value);
    }

    /**
     * @param  mixed  $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->__unset((string) $offset);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->__fields);
    }

    /**
     * @return \Traversable<string, mixed>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->__fields);
    }

    /**
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return $this->__fields;
    }

    /**
     * Turn a field into the object, or list of objects, it describes.
     *
     * @param  class-string<self>|array<int, class-string<self>>  $type
     * @param  mixed  $value
     * @return mixed
     */
    private function hydrate(string|array $type, mixed $value): mixed
    {
        if (is_array($type)) {
            $class = $type[0];

            if (! is_iterable($value)) {
                return $value;
            }

            return array_map(
                fn ($item) => $this->hydrate($class, $item),
                is_array($value) ? $value : iterator_to_array($value)
            );
        }

        if ($value instanceof self) {
            return $value;
        }

        return is_array($value) || is_object($value) ? $type::from($value) : $value;
    }

    /**
     * Turn a value into something json_encode() can send.
     *
     * @param  mixed  $value
     * @return mixed
     */
    private static function normalize(mixed $value): mixed
    {
        if ($value instanceof self) {
            return $value->toArray();
        }

        if ($value instanceof JsonSerializable) {
            return $value->jsonSerialize();
        }

        if (is_array($value)) {
            return array_map([self::class, 'normalize'], $value);
        }

        return $value;
    }
}
