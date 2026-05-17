<?php

declare(strict_types=1);

namespace CommonPHP\Session\Tests\Unit;

use CommonPHP\Session\Contracts\FlashBagInterface;
use CommonPHP\Session\Contracts\SessionBagInterface;
use CommonPHP\Session\Contracts\SessionDriverInterface;
use CommonPHP\Session\Drivers\NativeSessionDriver;
use CommonPHP\Session\Enums\SessionStatus;
use CommonPHP\Session\Exceptions\CorruptSessionDataException;
use CommonPHP\Session\Exceptions\InvalidSessionDriverException;
use CommonPHP\Session\Exceptions\SessionDriverException;
use CommonPHP\Session\Exceptions\SessionNotStartedException;
use CommonPHP\Session\SessionManager;
use CommonPHP\Session\Tests\Fixtures\ExplodingSessionDriver;
use CommonPHP\Session\Tests\Fixtures\MemorySessionDriver;
use CommonPHP\Session\Tests\Fixtures\ThrowingConstructorSessionDriver;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

final class SessionManagerTest extends TestCase
{
    public function testItUsesANativeDriverByDefaultAndCanBuildANativeManager(): void
    {
        $default = new SessionManager();
        $native = SessionManager::native(['read_and_close' => false], 'CUSTOMSESSID', 'custom-id');

        self::assertInstanceOf(NativeSessionDriver::class, $default->getDriver());
        self::assertInstanceOf(NativeSessionDriver::class, $native->getDriver());
    }

    public function testItStartsSavesInvalidatesAndReportsStatus(): void
    {
        $driver = new MemorySessionDriver();
        $session = new SessionManager($driver);

        self::assertSame(SessionStatus::None, $session->status());
        self::assertFalse($session->isStarted());
        self::assertSame($session, $session->start());
        self::assertTrue($session->isStarted());
        self::assertSame(1, $driver->startCalls);

        self::assertSame($session, $session->save());
        self::assertFalse($session->isStarted());
        self::assertSame(1, $driver->saveCalls);

        $session->start()->set('name', 'Ada');
        self::assertSame($session, $session->invalidate());
        self::assertFalse($session->isStarted());
        self::assertSame([], $driver->payload());
        self::assertSame(1, $driver->invalidateCalls);
    }

    public function testItDelegatesIdNameAndRegenerationToTheDriver(): void
    {
        $driver = new MemorySessionDriver();
        $session = new SessionManager($driver);

        self::assertSame($session, $session->setId('custom-id'));
        self::assertSame('custom-id', $session->id());
        self::assertSame($session, $session->setName('CUSTOMSESSID'));
        self::assertSame('CUSTOMSESSID', $session->name());

        $session->start();
        self::assertSame('memory-id-1', $session->regenerateId(false));
        self::assertFalse($driver->lastDeleteOldSession);
        self::assertSame(1, $driver->regenerateCalls);
    }

    public function testItCanSwitchDriversBeforeStart(): void
    {
        $session = new SessionManager(new MemorySessionDriver('first'));
        $driver = new MemorySessionDriver('second');

        self::assertSame($session, $session->useDriver($driver));
        self::assertSame($driver, $session->getDriver());

        self::assertSame($session, $session->setDriver(
            MemorySessionDriver::class,
            ['sessionId' => 'configured', 'sessionName' => 'CONFIGUREDSESSID'],
        ));
        self::assertInstanceOf(MemorySessionDriver::class, $session->getDriver());
        self::assertSame('configured', $session->id());
        self::assertSame('CONFIGUREDSESSID', $session->name());
    }

    public function testItRejectsInvalidDriverClassesAndConstructorFailures(): void
    {
        $session = new SessionManager(new MemorySessionDriver());

        try {
            $session->setDriver(stdClass::class);
            self::fail('Expected invalid driver exception.');
        } catch (InvalidSessionDriverException $exception) {
            self::assertStringContainsString(SessionDriverInterface::class, $exception->getMessage());
        }

        try {
            $session->setDriver(ThrowingConstructorSessionDriver::class);
            self::fail('Expected driver creation exception.');
        } catch (InvalidSessionDriverException $exception) {
            self::assertInstanceOf(RuntimeException::class, $exception->getPrevious());
        }
    }

    public function testItRejectsDriverInstanceConfigurationAndStartedDriverSwitches(): void
    {
        $session = new SessionManager(new MemorySessionDriver());

        $this->expectException(InvalidSessionDriverException::class);
        $session->setDriver(new MemorySessionDriver(), ['sessionId' => 'ignored']);
    }

    public function testItCannotSwitchDriversAfterStart(): void
    {
        $session = new SessionManager(new MemorySessionDriver());
        $session->start();

        $this->expectException(InvalidSessionDriverException::class);

        $session->useDriver(new MemorySessionDriver());
    }

    public function testItReadsWritesAndRemovesRootValues(): void
    {
        $driver = new MemorySessionDriver(started: true);
        $session = new SessionManager($driver);

        self::assertSame('fallback', $session->get('missing', 'fallback'));
        self::assertFalse($session->has('user'));
        self::assertSame($session, $session->set('user', 'Ada'));
        self::assertTrue($session->has('user'));
        self::assertSame('Ada', $session->get('user'));
        self::assertSame('Ada', $session->pull('user'));
        self::assertSame('fallback', $session->remove('user', 'fallback'));
        self::assertSame([], $session->all());
    }

    public function testItReplacesClearsAndExposesTheRootBag(): void
    {
        $driver = new MemorySessionDriver(started: true);
        $session = new SessionManager($driver);
        $root = $session->bag();

        self::assertInstanceOf(SessionBagInterface::class, $root);
        self::assertSame($root, $session->bag());
        $root->set('theme', 'dark');
        self::assertSame('dark', $session->get('theme'));

        self::assertSame($session, $session->replace(['locale' => 'en']));
        self::assertSame(['locale' => 'en'], $session->all());
        self::assertNotSame($root, $session->bag());

        self::assertSame($session, $session->clear());
        self::assertSame([], $session->all());
    }

    public function testItProvidesNamedBagsAndDetectsCorruptNamespaces(): void
    {
        $driver = new MemorySessionDriver(started: true);
        $session = new SessionManager($driver);
        $bag = $session->bag('preferences');

        self::assertInstanceOf(SessionBagInterface::class, $bag);
        self::assertSame($bag, $session->bag('preferences'));
        $bag->set('theme', 'dark');
        self::assertSame(['preferences' => ['theme' => 'dark']], $session->all());

        $session->set('preferences', 'corrupt');

        $this->expectException(CorruptSessionDataException::class);
        $this->expectExceptionMessage('preferences');

        $session->bag('preferences');
    }

    public function testItProvidesFlashBagsAndDropsCachedNamespacesWhenOverwritten(): void
    {
        $driver = new MemorySessionDriver(started: true);
        $session = new SessionManager($driver);
        $flash = $session->flash();

        self::assertInstanceOf(FlashBagInterface::class, $flash);
        self::assertSame($flash, $session->flash());
        $flash->add('notice', 'Ready');
        self::assertSame(['Ready'], $session->flash()->peek('notice'));

        $session->set(SessionManager::DEFAULT_FLASH_NAMESPACE, ['notice' => ['Updated']]);
        self::assertNotSame($flash, $session->flash());
        self::assertSame(['Updated'], $session->flash()->get('notice'));
    }

    public function testDataAccessRequiresStartedDrivers(): void
    {
        $session = new SessionManager(new MemorySessionDriver());

        $this->expectException(SessionNotStartedException::class);

        $session->get('user');
    }

    public function testLifecycleOperationsThatNeedActiveDataRejectNotStartedDrivers(): void
    {
        foreach (['save', 'invalidate', 'regenerate'] as $operation) {
            $session = new SessionManager(new MemorySessionDriver());

            try {
                match ($operation) {
                    'save' => $session->save(),
                    'invalidate' => $session->invalidate(),
                    'regenerate' => $session->regenerateId(),
                };
                self::fail('Expected not-started exception for ' . $operation . '.');
            } catch (SessionNotStartedException) {
                self::assertTrue(true);
            }
        }
    }

    public function testSessionExceptionsPassThroughAndUnexpectedDriverErrorsAreWrapped(): void
    {
        $notStarted = new SessionManager(new MemorySessionDriver());

        try {
            $notStarted->set('user', 'Ada');
            self::fail('Expected session not started exception.');
        } catch (SessionNotStartedException $exception) {
            self::assertNull($exception->getPrevious());
        }

        $exploding = new SessionManager(new ExplodingSessionDriver('start'));

        try {
            $exploding->start();
            self::fail('Expected wrapped driver exception.');
        } catch (SessionDriverException $exception) {
            self::assertInstanceOf(RuntimeException::class, $exception->getPrevious());
            self::assertStringContainsString('start', $exception->getMessage());
        }
    }

    public function testUnexpectedDataAccessErrorsAreWrapped(): void
    {
        $session = new SessionManager(new ExplodingSessionDriver('data'));

        $this->expectException(SessionDriverException::class);

        $session->all();
    }
}
