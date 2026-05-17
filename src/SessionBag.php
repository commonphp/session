<?php

declare(strict_types=1);

namespace CommonPHP\Session;

use ArrayIterator;
use CommonPHP\Session\Contracts\SessionBagInterface;
use CommonPHP\Session\Exceptions\CorruptSessionDataException;
use Traversable;

/**
 * @implements SessionBagInterface
 */
final class SessionBag implements SessionBagInterface
{
    /**
     * @var mixed
     */
    private mixed $values;

    /**
     * @param array<string, mixed> $values
     */
    public function __construct(array &$values, private readonly ?string $name = null)
    {
        $this->values = &$values;
    }

    /**
     * @param array<string, mixed> $values
     */
    public static function create(array $values = []): self
    {
        return new self($values);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $values = &$this->values();

        return array_key_exists($key, $values) ? $values[$key] : $default;
    }

    public function set(string $key, mixed $value): static
    {
        $values = &$this->values();
        $values[$key] = $value;

        return $this;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->values());
    }

    public function remove(string $key, mixed $default = null): mixed
    {
        if (!$this->has($key)) {
            return $default;
        }

        $values = &$this->values();
        $value = $values[$key];
        unset($values[$key]);

        return $value;
    }

    public function pull(string $key, mixed $default = null): mixed
    {
        return $this->remove($key, $default);
    }

    public function replace(array $values): static
    {
        $current = &$this->values();
        $current = $values;

        return $this;
    }

    public function all(): array
    {
        return $this->values();
    }

    public function clear(): static
    {
        $values = &$this->values();
        $values = [];

        return $this;
    }

    public function isEmpty(): bool
    {
        return $this->values() === [];
    }

    public function count(): int
    {
        return count($this->values());
    }

    /**
     * @return Traversable<string, mixed>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->values());
    }

    /**
     * @return array<string, mixed>
     */
    private function &values(): array
    {
        if (!is_array($this->values)) {
            throw CorruptSessionDataException::forBag($this->name);
        }

        return $this->values;
    }
}
