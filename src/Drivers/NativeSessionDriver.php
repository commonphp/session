<?php

declare(strict_types=1);

namespace CommonPHP\Session\Drivers;

use CommonPHP\Session\Contracts\AbstractSessionDriver;
use CommonPHP\Session\Enums\SessionStatus;
use CommonPHP\Session\Exceptions\SessionNotStartedException;
use CommonPHP\Session\Exceptions\SessionStartException;
use CommonPHP\Session\Exceptions\SessionStorageException;
use Throwable;

final class NativeSessionDriver extends AbstractSessionDriver
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        private readonly array $options = [],
        private ?string $configuredName = null,
        private ?string $configuredId = null,
    ) {
        if ($this->configuredName === '') {
            throw SessionStorageException::forOperation('set the session name: value cannot be empty');
        }

        if ($this->configuredId === '') {
            throw SessionStorageException::forOperation('set the session id: value cannot be empty');
        }
    }

    public function start(): void
    {
        $this->assertSessionSupport();

        if ($this->status() === SessionStatus::Active) {
            return;
        }

        $this->applyConfiguredName();
        $this->applyConfiguredId();

        $started = $this->captureSessionWarning(
            fn (): bool => session_start($this->options),
            static fn (?string $warning): SessionStartException => SessionStartException::failed($warning),
        );

        if ($started !== true) {
            throw SessionStartException::failed();
        }

        if (!isset($_SESSION) || !is_array($_SESSION)) {
            $_SESSION = [];
        }
    }

    public function save(): void
    {
        if ($this->status() !== SessionStatus::Active) {
            throw SessionNotStartedException::forOperation('save session data');
        }

        $saved = $this->captureSessionWarning(
            static fn (): bool => session_write_close(),
            static fn (?string $warning): SessionStorageException => SessionStorageException::forOperation(
                'save' . ($warning === null ? '' : ': ' . $warning),
            ),
        );

        if ($saved !== true) {
            throw SessionStorageException::forOperation('save');
        }
    }

    public function invalidate(): void
    {
        if ($this->status() !== SessionStatus::Active) {
            throw SessionNotStartedException::forOperation('invalidate the session');
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            if (!headers_sent()) {
                setcookie(
                    session_name(),
                    '',
                    [
                        'expires' => time() - 42000,
                        'path' => $params['path'],
                        'domain' => $params['domain'],
                        'secure' => $params['secure'],
                        'httponly' => $params['httponly'],
                        'samesite' => $params['samesite'] ?? '',
                    ],
                );
            }
        }

        $destroyed = $this->captureSessionWarning(
            static fn (): bool => session_destroy(),
            static fn (?string $warning): SessionStorageException => SessionStorageException::forOperation(
                'destroy' . ($warning === null ? '' : ': ' . $warning),
            ),
        );

        if ($destroyed !== true) {
            throw SessionStorageException::forOperation('destroy');
        }
    }

    public function regenerateId(bool $deleteOldSession = true): string
    {
        if ($this->status() !== SessionStatus::Active) {
            throw SessionNotStartedException::forOperation('regenerate the session id');
        }

        $regenerated = $this->captureSessionWarning(
            static fn (): bool => session_regenerate_id($deleteOldSession),
            static fn (?string $warning): SessionStorageException => SessionStorageException::forOperation(
                'regenerate id' . ($warning === null ? '' : ': ' . $warning),
            ),
        );

        if ($regenerated !== true) {
            throw SessionStorageException::forOperation('regenerate id');
        }

        return session_id();
    }

    public function status(): SessionStatus
    {
        return SessionStatus::fromNative(session_status());
    }

    public function id(): string
    {
        return session_id();
    }

    public function setId(string $id): void
    {
        $this->assertCanConfigure('set the session id');
        $this->assertNotEmpty($id, 'set the session id');

        $applied = $this->captureSessionWarning(
            static fn (): string|false => session_id($id),
            static fn (?string $warning): SessionStorageException => SessionStorageException::forOperation(
                'set id' . ($warning === null ? '' : ': ' . $warning),
            ),
        );

        if ($applied === false) {
            throw SessionStorageException::forOperation('set id');
        }

        $this->configuredId = $id;
    }

    public function name(): string
    {
        return session_name();
    }

    public function setName(string $name): void
    {
        $this->assertCanConfigure('set the session name');
        $this->assertNotEmpty($name, 'set the session name');

        $applied = $this->captureSessionWarning(
            static fn (): string|false => session_name($name),
            static fn (?string $warning): SessionStorageException => SessionStorageException::forOperation(
                'set name' . ($warning === null ? '' : ': ' . $warning),
            ),
        );

        if ($applied === false) {
            throw SessionStorageException::forOperation('set name');
        }

        $this->configuredName = $name;
    }

    public function &data(): array
    {
        $this->assertCanAccessData('access session data');

        if (!isset($_SESSION) || !is_array($_SESSION)) {
            $_SESSION = [];
        }

        return $_SESSION;
    }

    private function applyConfiguredName(): void
    {
        if ($this->configuredName !== null) {
            $applied = $this->captureSessionWarning(
                fn (): string|false => session_name($this->configuredName),
                static fn (?string $warning): SessionStartException => SessionStartException::failed($warning),
            );

            if ($applied === false) {
                throw SessionStartException::failed('Unable to set the session name.');
            }
        }
    }

    private function applyConfiguredId(): void
    {
        if ($this->configuredId !== null) {
            $applied = $this->captureSessionWarning(
                fn (): string|false => session_id($this->configuredId),
                static fn (?string $warning): SessionStartException => SessionStartException::failed($warning),
            );

            if ($applied === false) {
                throw SessionStartException::failed('Unable to set the session id.');
            }
        }
    }

    private function assertCanConfigure(string $operation): void
    {
        if ($this->status() === SessionStatus::Active) {
            throw SessionStorageException::forOperation($operation);
        }
    }

    private function assertNotEmpty(string $value, string $operation): void
    {
        if ($value === '') {
            throw SessionStorageException::forOperation($operation . ': value cannot be empty');
        }
    }

    /**
     * @template T
     * @param callable(): T $operation
     * @param callable(?string): Throwable $exception
     * @return T
     */
    private function captureSessionWarning(callable $operation, callable $exception): mixed
    {
        $warning = null;

        set_error_handler(
            static function (int $severity, string $message) use (&$warning): bool {
                $warning = $message;

                return true;
            },
        );

        try {
            return $operation();
        } catch (Throwable $throwable) {
            throw $throwable;
        } finally {
            restore_error_handler();

            if ($warning !== null) {
                throw $exception($warning);
            }
        }
    }
}
