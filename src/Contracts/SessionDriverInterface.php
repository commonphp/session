<?php

declare(strict_types=1);

namespace CommonPHP\Session\Contracts;

use CommonPHP\Runtime\Contracts\DriverInterface;
use CommonPHP\Session\Enums\SessionStatus;

interface SessionDriverInterface extends DriverInterface
{
    public function start(): void;

    public function save(): void;

    public function invalidate(): void;

    public function regenerateId(bool $deleteOldSession = true): string;

    public function status(): SessionStatus;

    public function id(): string;

    public function setId(string $id): void;

    public function name(): string;

    public function setName(string $name): void;

    /**
     * @return array<string, mixed>
     */
    public function &data(): array;
}
