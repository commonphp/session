<?php

declare(strict_types=1);

namespace CommonPHP\Session\Tests\Unit;

use CommonPHP\Session\Contracts\AbstractSessionDriver;
use CommonPHP\Session\Contracts\FlashBagInterface;
use CommonPHP\Session\Contracts\SessionBagInterface;
use CommonPHP\Session\Contracts\SessionDriverInterface;
use CommonPHP\Session\Contracts\SessionInterface;
use CommonPHP\Session\Enums\SessionStatus;
use CommonPHP\Session\Exceptions\CorruptSessionDataException;
use CommonPHP\Session\Exceptions\InvalidSessionDriverException;
use CommonPHP\Session\Exceptions\SessionDriverException;
use CommonPHP\Session\Exceptions\SessionException;
use CommonPHP\Session\Exceptions\SessionNotStartedException;
use CommonPHP\Session\Exceptions\SessionStartException;
use CommonPHP\Session\Exceptions\SessionStorageException;
use CommonPHP\Session\SessionManager;
use CommonPHP\Session\Tests\Fixtures\MemorySessionDriver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ContractsAndExceptionsTest extends TestCase
{
    public function testSessionStatusMapsNativeValuesAndReportsActiveState(): void
    {
        self::assertSame(SessionStatus::Disabled, SessionStatus::fromNative(PHP_SESSION_DISABLED));
        self::assertSame(SessionStatus::None, SessionStatus::fromNative(PHP_SESSION_NONE));
        self::assertSame(SessionStatus::Active, SessionStatus::fromNative(PHP_SESSION_ACTIVE));
        self::assertSame(SessionStatus::None, SessionStatus::fromNative(999));
        self::assertTrue(SessionStatus::Active->isActive());
        self::assertFalse(SessionStatus::None->isActive());
    }

    public function testConcreteClassesImplementTheirContracts(): void
    {
        $driver = new MemorySessionDriver();
        $manager = new SessionManager($driver);
        $manager->start();

        self::assertInstanceOf(AbstractSessionDriver::class, $driver);
        self::assertInstanceOf(SessionDriverInterface::class, $driver);
        self::assertInstanceOf(SessionInterface::class, $manager);
        self::assertInstanceOf(SessionBagInterface::class, $manager->bag());
        self::assertInstanceOf(FlashBagInterface::class, $manager->flash());
    }

    public function testAbstractDriverProvidesNameAndGuardHelpers(): void
    {
        $driver = new MemorySessionDriver();

        self::assertSame(MemorySessionDriver::class, $driver->getName());
        self::assertSame(SessionStatus::None, $driver->status());

        $this->expectException(SessionNotStartedException::class);

        $driver->data();
    }

    #[DataProvider('exceptionFactoryProvider')]
    public function testExceptionFactoriesBuildHelpfulMessages(SessionException $exception, string $expectedText): void
    {
        self::assertStringContainsString($expectedText, $exception->getMessage());
    }

    public function testExceptionFactoriesPreservePreviousExceptions(): void
    {
        $previous = new RuntimeException('Original failure.');

        self::assertSame($previous, SessionDriverException::forOperation('start', $previous)->getPrevious());
        self::assertSame($previous, SessionStorageException::forOperation('save', $previous)->getPrevious());
        self::assertSame($previous, SessionStartException::failed('Bad path.', $previous)->getPrevious());
        self::assertSame(
            $previous,
            InvalidSessionDriverException::forCreation(MemorySessionDriver::class, $previous)->getPrevious(),
        );
    }

    public static function exceptionFactoryProvider(): iterable
    {
        yield 'driver operation' => [SessionDriverException::forOperation('start'), 'start'];
        yield 'invalid class' => [InvalidSessionDriverException::forClass('BadDriver'), 'BadDriver'];
        yield 'invalid creation' => [
            InvalidSessionDriverException::forCreation('BadDriver', new RuntimeException()),
            'could not be created',
        ];
        yield 'started switch' => [InvalidSessionDriverException::cannotSwitchStartedSession(), 'Cannot switch'];
        yield 'not started' => [SessionNotStartedException::forOperation('read'), 'before the session has started'];
        yield 'start disabled' => [SessionStartException::disabled(), 'disabled'];
        yield 'start failed' => [SessionStartException::failed('Path missing.'), 'Path missing'];
        yield 'storage operation' => [SessionStorageException::forOperation('save'), 'save'];
        yield 'corrupt namespace' => [CorruptSessionDataException::forNamespace('prefs'), 'prefs'];
        yield 'corrupt bag' => [CorruptSessionDataException::forBag('flash'), 'flash'];
    }
}
