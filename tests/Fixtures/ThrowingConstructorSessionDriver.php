<?php

declare(strict_types=1);

namespace CommonPHP\Session\Tests\Fixtures;

use CommonPHP\Session\Contracts\AbstractSessionDriver;
use CommonPHP\Session\Enums\SessionStatus;
use RuntimeException;

final class ThrowingConstructorSessionDriver extends AbstractSessionDriver
{
    public function __construct()
    {
        throw new RuntimeException('Constructor exploded.');
    }

    public function start(): void
    {
    }

    public function save(): void
    {
    }

    public function invalidate(): void
    {
    }

    public function regenerateId(bool $deleteOldSession = true): string
    {
        return 'unreachable';
    }

    public function status(): SessionStatus
    {
        return SessionStatus::None;
    }

    public function id(): string
    {
        return 'unreachable';
    }

    public function setId(string $id): void
    {
    }

    public function name(): string
    {
        return 'UNREACHABLE';
    }

    public function setName(string $name): void
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function &data(): array
    {
        $data = [];

        return $data;
    }
}
