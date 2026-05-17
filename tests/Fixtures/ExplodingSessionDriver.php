<?php

declare(strict_types=1);

namespace CommonPHP\Session\Tests\Fixtures;

use CommonPHP\Session\Contracts\AbstractSessionDriver;
use CommonPHP\Session\Enums\SessionStatus;
use RuntimeException;

final class ExplodingSessionDriver extends AbstractSessionDriver
{
    /**
     * @var array<string, mixed>
     */
    private array $payload = [];

    public function __construct(private readonly string $operation = 'start')
    {
    }

    public function start(): void
    {
        $this->explodeIf('start');
    }

    public function save(): void
    {
        $this->explodeIf('save');
    }

    public function invalidate(): void
    {
        $this->explodeIf('invalidate');
    }

    public function regenerateId(bool $deleteOldSession = true): string
    {
        $this->explodeIf('regenerate');

        return 'exploding-id';
    }

    public function status(): SessionStatus
    {
        return SessionStatus::Active;
    }

    public function id(): string
    {
        return 'exploding-id';
    }

    public function setId(string $id): void
    {
        $this->explodeIf('setId');
    }

    public function name(): string
    {
        return 'EXPLODINGSESSID';
    }

    public function setName(string $name): void
    {
        $this->explodeIf('setName');
    }

    /**
     * @return array<string, mixed>
     */
    public function &data(): array
    {
        $this->explodeIf('data');

        return $this->payload;
    }

    private function explodeIf(string $operation): void
    {
        if ($this->operation === $operation) {
            throw new RuntimeException('Exploded during ' . $operation . '.');
        }
    }
}
