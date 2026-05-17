<?php

declare(strict_types=1);

namespace CommonPHP\Session\Exceptions;

class CorruptSessionDataException extends SessionException
{
    public static function forNamespace(string $namespace): self
    {
        return new self('Session namespace "' . $namespace . '" contains non-array data.');
    }

    public static function forBag(?string $name = null): self
    {
        $label = $name ?? 'session bag';

        return new self('Session bag "' . $label . '" contains non-array data.');
    }

}
