<?php

declare(strict_types=1);

namespace CommonPHP\Session\Exceptions;

class SessionNotStartedException extends SessionException
{
    public static function forOperation(string $operation): self
    {
        return new self('Cannot ' . $operation . ' before the session has started.');
    }

}
