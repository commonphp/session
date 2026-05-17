<?php

declare(strict_types=1);

namespace CommonPHP\Session\Contracts;

abstract class AbstractSessionDriver implements SessionDriverInterface
{
    public function getName(): string
    {
        return static::class;
    }
}