<?php

declare(strict_types=1);

namespace CommonPHP\Session\Contracts;

use Countable;
use IteratorAggregate;

/**
 * @extends IteratorAggregate<string, list<mixed>>
 */
interface FlashBagInterface extends Countable, IteratorAggregate
{
    public function add(string $type, mixed $message): static;

    /**
     * @param array<int, mixed> $messages
     */
    public function set(string $type, array $messages): static;

    /**
     * @return list<mixed>
     */
    public function get(string $type, array $default = []): array;

    /**
     * @return list<mixed>
     */
    public function peek(string $type, array $default = []): array;

    public function has(string $type): bool;

    /**
     * @return array<string, list<mixed>>
     */
    public function all(): array;

    /**
     * @return array<string, list<mixed>>
     */
    public function peekAll(): array;

    public function clear(?string $type = null): static;
}
