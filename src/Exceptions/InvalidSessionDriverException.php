<?php

declare(strict_types=1);

namespace CommonPHP\Session\Exceptions;

use CommonPHP\Session\Contracts\SessionDriverInterface;
use Throwable;

class InvalidSessionDriverException extends SessionException
{
    public static function forClass(string $driverClass): self
    {
        return new self('Session driver "' . $driverClass . '" must implement ' . SessionDriverInterface::class . '.');
    }

    public static function forCreation(string $driverClass, Throwable $previous): self
    {
        return new self('Session driver "' . $driverClass . '" could not be created.', previous: $previous);
    }

    public static function cannotSwitchStartedSession(): self
    {
        return new self('Cannot switch session drivers after the session has started.');
    }

}
