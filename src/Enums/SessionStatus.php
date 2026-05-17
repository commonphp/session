<?php

declare(strict_types=1);

namespace CommonPHP\Session\Enums;

enum SessionStatus
{
    case Disabled;
    case None;
    case Active;

    public static function fromNative(int $status): self
    {
        return match ($status) {
            PHP_SESSION_DISABLED => self::Disabled,
            PHP_SESSION_ACTIVE => self::Active,
            default => self::None,
        };
    }

    public function isActive(): bool
    {
        return $this === self::Active;
    }
}
