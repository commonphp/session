<?php

declare(strict_types=1);

namespace CommonPHP\Session\Tests\Unit;

use CommonPHP\Session\Drivers\NativeSessionDriver;
use CommonPHP\Session\Enums\SessionStatus;
use CommonPHP\Session\Exceptions\SessionNotStartedException;
use CommonPHP\Session\Exceptions\SessionStartException;
use CommonPHP\Session\Exceptions\SessionStorageException;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class NativeSessionDriverTest extends TestCase
{
    public function testConstructorRejectsEmptyNamesAndIds(): void
    {
        $this->expectException(SessionStorageException::class);

        new NativeSessionDriver(configuredName: '');
    }

    public function testConstructorRejectsEmptyIds(): void
    {
        $this->expectException(SessionStorageException::class);

        new NativeSessionDriver(configuredId: '');
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testItStartsExposesDataByReferenceRegeneratesAndSaves(): void
    {
        $this->prepareNativeSessionEnvironment();

        $driver = new NativeSessionDriver([], 'KTSAASTEST', 'codex-session-id');

        self::assertSame(SessionStatus::None, $driver->status());

        $driver->start();
        self::assertSame(SessionStatus::Active, $driver->status());
        self::assertSame('KTSAASTEST', $driver->name());
        self::assertSame('codex-session-id', $driver->id());

        $data = &$driver->data();
        $data['user'] = 'Ada';
        self::assertSame('Ada', $driver->data()['user']);

        $newId = $driver->regenerateId(false);
        self::assertNotSame('', $newId);
        self::assertSame($newId, $driver->id());

        $driver->start();
        self::assertSame('Ada', $driver->data()['user']);

        $driver->save();
        self::assertSame(SessionStatus::None, $driver->status());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testItInvalidatesTheActiveSession(): void
    {
        $this->prepareNativeSessionEnvironment();

        $driver = new NativeSessionDriver([], 'KTSAASTEST', 'invalidate-session-id');
        $driver->start();
        $data = &$driver->data();
        $data['user'] = 'Ada';

        $driver->invalidate();

        self::assertSame(SessionStatus::None, $driver->status());
        self::assertSame([], $_SESSION);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testItRejectsDataSaveInvalidateAndRegenerateBeforeStart(): void
    {
        $this->prepareNativeSessionEnvironment();

        $driver = new NativeSessionDriver([], 'KTSAASTEST', 'not-started-id');

        foreach (['data', 'save', 'invalidate', 'regenerate'] as $operation) {
            try {
                match ($operation) {
                    'data' => $driver->data(),
                    'save' => $driver->save(),
                    'invalidate' => $driver->invalidate(),
                    'regenerate' => $driver->regenerateId(),
                };
                self::fail('Expected not-started exception for ' . $operation . '.');
            } catch (SessionNotStartedException) {
                self::assertTrue(true);
            }
        }
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testItAllowsConfiguringNameAndIdBeforeStartOnly(): void
    {
        $this->prepareNativeSessionEnvironment();

        $driver = new NativeSessionDriver();
        $driver->setName('BEFORESTART');
        $driver->setId('before-start-id');

        self::assertSame('BEFORESTART', $driver->name());
        self::assertSame('before-start-id', $driver->id());

        $driver->start();

        try {
            $driver->setName('AFTERSTART');
            self::fail('Expected active session name configuration failure.');
        } catch (SessionStorageException) {
            self::assertTrue(true);
        }

        try {
            $driver->setId('after-start-id');
            self::fail('Expected active session id configuration failure.');
        } catch (SessionStorageException) {
            self::assertTrue(true);
        }
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testItRejectsEmptyNameAndIdConfiguration(): void
    {
        $this->prepareNativeSessionEnvironment();

        $driver = new NativeSessionDriver();

        try {
            $driver->setName('');
            self::fail('Expected empty name failure.');
        } catch (SessionStorageException $exception) {
            self::assertStringContainsString('empty', $exception->getMessage());
        }

        try {
            $driver->setId('');
            self::fail('Expected empty id failure.');
        } catch (SessionStorageException $exception) {
            self::assertStringContainsString('empty', $exception->getMessage());
        }
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testItConvertsNativeStartWarningsToSessionStartExceptions(): void
    {
        $directory = $this->prepareNativeSessionEnvironment();
        $file = $directory . '/not-a-directory';
        file_put_contents($file, 'nope');
        ini_set('session.save_path', $file);

        $driver = new NativeSessionDriver([], 'KTSAASTEST', 'bad-save-path-id');

        $this->expectException(SessionStartException::class);

        $driver->start();
    }

    private function prepareNativeSessionEnvironment(): string
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_abort();
        }

        $_SESSION = [];
        $_COOKIE = [];

        $directory = dirname(__DIR__, 2) . '/.phpunit.cache/sessions/' . bin2hex(random_bytes(8));

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        ini_set('session.save_path', $directory);
        ini_set('session.use_cookies', '0');
        ini_set('session.cache_limiter', '');

        return $directory;
    }
}
