<?php

declare(strict_types=1);

namespace CommonPHP\Session\Exceptions;

use Throwable;

class SessionDriverException extends SessionException
{
    public static function forOperation(string $operation, ?Throwable $previous = null): self
    {
        return new self('Session driver operation "' . $operation . '" failed.', previous: $previous);
    }

}
