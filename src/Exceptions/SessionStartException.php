<?php

declare(strict_types=1);

namespace CommonPHP\Session\Exceptions;

use Throwable;

class SessionStartException extends SessionException
{
    public static function disabled(): self
    {
        return new self('Sessions are disabled in this PHP environment.');
    }

    public static function failed(?string $reason = null, ?Throwable $previous = null): self
    {
        $message = 'Failed to start the session.';

        if ($reason !== null && $reason !== '') {
            $message .= ' ' . $reason;
        }

        return new self($message, previous: $previous);
    }

}
