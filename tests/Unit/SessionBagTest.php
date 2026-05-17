<?php

declare(strict_types=1);

namespace CommonPHP\Session\Tests\Unit;

use CommonPHP\Session\Contracts\SessionBagInterface;
use CommonPHP\Session\Exceptions\CorruptSessionDataException;
use CommonPHP\Session\SessionBag;
use PHPUnit\Framework\TestCase;

final class SessionBagTest extends TestCase
{
    public function testItReadsAndWritesValuesByKey(): void
    {
        $values = ['present' => null];
        $bag = new SessionBag($values);

        self::assertInstanceOf(SessionBagInterface::class, $bag);
        self::assertTrue($bag->has('present'));
        self::assertNull($bag->get('present'));
        self::assertSame('fallback', $bag->get('missing', 'fallback'));
        self::assertSame($bag, $bag->set('name', 'Ada'));
        self::assertSame('Ada', $bag->get('name'));
        self::assertSame(['present' => null, 'name' => 'Ada'], $values);
    }

    public function testRemoveAndPullReturnRemovedValuesOrDefaults(): void
    {
        $values = ['one' => 1, 'two' => 2];
        $bag = new SessionBag($values);

        self::assertSame(1, $bag->remove('one'));
        self::assertSame('missing', $bag->remove('one', 'missing'));
        self::assertSame(2, $bag->pull('two'));
        self::assertSame([], $bag->all());
    }

    public function testReplaceClearCountIteratorAndEmptyStateReflectTheBackingArray(): void
    {
        $values = [];
        $bag = new SessionBag($values);

        self::assertTrue($bag->isEmpty());
        self::assertSame(0, count($bag));

        self::assertSame($bag, $bag->replace(['theme' => 'dark', 'locale' => 'en']));
        self::assertSame(['theme' => 'dark', 'locale' => 'en'], $values);
        self::assertFalse($bag->isEmpty());
        self::assertSame(2, count($bag));
        self::assertSame($bag->all(), iterator_to_array($bag->getIterator()));

        self::assertSame($bag, $bag->clear());
        self::assertSame([], $values);
        self::assertTrue($bag->isEmpty());
    }

    public function testCreateBuildsAnIndependentBag(): void
    {
        $bag = SessionBag::create(['started' => true]);

        self::assertTrue($bag->get('started'));
        $bag->set('name', 'Grace');

        self::assertSame(['started' => true, 'name' => 'Grace'], $bag->all());
    }

    public function testItThrowsWhenTheReferencedBagDataBecomesCorrupt(): void
    {
        $values = ['safe' => true];
        $bag = new SessionBag($values, 'profile');
        $values = 'corrupt';

        $this->expectException(CorruptSessionDataException::class);
        $this->expectExceptionMessage('profile');

        $bag->all();
    }
}
