<?php

declare(strict_types=1);

namespace CommonPHP\Session\Tests\Fixtures;

use CommonPHP\Session\Contracts\AbstractSessionDriver;
use CommonPHP\Session\Enums\SessionStatus;

final class MemorySessionDriver extends AbstractSessionDriver
{
    /**
     * @var array<string, mixed>
     */
    private array $payload;

    public int $startCalls = 0;

    public int $saveCalls = 0;

    public int $invalidateCalls = 0;

    public int $regenerateCalls = 0;

    public ?bool $lastDeleteOldSession = null;

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        private string $sessionId = 'memory-id',
        private string $sessionName = 'MEMORYSESSID',
        array $payload = [],
        private bool $started = false,
    ) {
        $this->payload = $payload;
    }

    public function start(): void
    {
        $this->startCalls++;
        $this->started = true;
    }

    public function save(): void
    {
        $this->assertCanAccessData('save session data');
        $this->saveCalls++;
        $this->started = false;
    }

    public function invalidate(): void
    {
        $this->assertCanAccessData('invalidate the session');
        $this->invalidateCalls++;
        $this->payload = [];
        $this->started = false;
    }

    public function regenerateId(bool $deleteOldSession = true): string
    {
        $this->assertCanAccessData('regenerate the session id');
        $this->regenerateCalls++;
        $this->lastDeleteOldSession = $deleteOldSession;
        $this->sessionId = 'memory-id-' . $this->regenerateCalls;

        return $this->sessionId;
    }

    public function status(): SessionStatus
    {
        return $this->started ? SessionStatus::Active : SessionStatus::None;
    }

    public function id(): string
    {
        return $this->sessionId;
    }

    public function setId(string $id): void
    {
        $this->sessionId = $id;
    }

    public function name(): string
    {
        return $this->sessionName;
    }

    public function setName(string $name): void
    {
        $this->sessionName = $name;
    }

    /**
     * @return array<string, mixed>
     */
    public function &data(): array
    {
        $this->assertCanAccessData('access memory session data');

        return $this->payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->payload;
    }
}
