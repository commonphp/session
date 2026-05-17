<?php

declare(strict_types=1);

namespace CommonPHP\Session\Contracts;

use Countable;
use IteratorAggregate;

/**
 * @extends IteratorAggregate<string, mixed>
 */
interface SessionBagInterface extends Countable, IteratorAggregate
{
    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $value): static;

    public function has(string $key): bool;

    public function remove(string $key, mixed $default = null): mixed;

    public function pull(string $key, mixed $default = null): mixed;

    /**
     * @param array<string, mixed> $values
     */
    public function replace(array $values): static;

    /**
     * @return array<string, mixed>
     */
    public function all(): array;

    public function clear(): static;

    public function isEmpty(): bool;
}
