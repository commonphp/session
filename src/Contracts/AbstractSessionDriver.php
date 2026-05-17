<?php

declare(strict_types=1);

namespace CommonPHP\Session\Contracts;

use CommonPHP\Session\Enums\SessionStatus;
use CommonPHP\Session\Exceptions\SessionNotStartedException;
use CommonPHP\Session\Exceptions\SessionStartException;

abstract class AbstractSessionDriver implements SessionDriverInterface
{
    public function getName(): string
    {
        return static::class;
    }

    public function status(): SessionStatus
    {
        return SessionStatus::None;
    }

    protected function assertCanAccessData(string $operation): void
    {
        if (!$this->status()->isActive()) {
            throw SessionNotStartedException::forOperation($operation);
        }
    }

    protected function assertSessionSupport(): void
    {
        if ($this->status() === SessionStatus::Disabled) {
            throw SessionStartException::disabled();
        }
    }
}
