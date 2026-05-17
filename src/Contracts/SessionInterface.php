<?php

declare(strict_types=1);

namespace CommonPHP\Session\Contracts;

use CommonPHP\Session\Enums\SessionStatus;

interface SessionInterface
{
    public function start(): static;

    public function save(): static;

    public function invalidate(): static;

    public function regenerateId(bool $deleteOldSession = true): string;

    public function status(): SessionStatus;

    public function isStarted(): bool;

    public function id(): string;

    public function setId(string $id): static;

    public function name(): string;

    public function setName(string $name): static;

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

    public function bag(?string $name = null): SessionBagInterface;

    public function flash(string $namespace = '_flash'): FlashBagInterface;
}
