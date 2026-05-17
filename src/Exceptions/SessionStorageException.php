<?php

declare(strict_types=1);

namespace CommonPHP\Session\Exceptions;

use Throwable;

class SessionStorageException extends SessionException
{
    public static function forOperation(string $operation, ?Throwable $previous = null): self
    {
        return new self('Session storage operation "' . $operation . '" failed.', previous: $previous);
    }

}
