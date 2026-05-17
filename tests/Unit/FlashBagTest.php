<?php

declare(strict_types=1);

namespace CommonPHP\Session\Tests\Unit;

use CommonPHP\Session\Contracts\FlashBagInterface;
use CommonPHP\Session\Exceptions\CorruptSessionDataException;
use CommonPHP\Session\FlashBag;
use PHPUnit\Framework\TestCase;

final class FlashBagTest extends TestCase
{
    public function testItAddsAndPeeksMessagesWithoutConsumingThem(): void
    {
        $messages = [];
        $bag = new FlashBag($messages);

        self::assertInstanceOf(FlashBagInterface::class, $bag);
        self::assertSame($bag, $bag->add('success', 'Saved'));
        self::assertSame($bag, $bag->add('success', 'Published'));
        self::assertTrue($bag->has('success'));
        self::assertSame(['Saved', 'Published'], $bag->peek('success'));
        self::assertSame(['Saved', 'Published'], $bag->peek('success'));
        self::assertSame(['success' => ['Saved', 'Published']], $messages);
    }

    public function testGetConsumesMessagesAndReturnsDefaultsWhenMissing(): void
    {
        $messages = ['notice' => ['Ready']];
        $bag = new FlashBag($messages);

        self::assertSame(['Ready'], $bag->get('notice'));
        self::assertSame([], $bag->get('notice'));
        self::assertSame(['default'], $bag->get('missing', ['default']));
        self::assertSame([], $messages);
    }

    public function testSetNormalizesIndexesAndHasIgnoresEmptyMessageLists(): void
    {
        $messages = [];
        $bag = new FlashBag($messages);

        self::assertSame($bag, $bag->set('errors', [2 => 'First', 4 => 'Second']));
        self::assertSame(['First', 'Second'], $bag->peek('errors'));
        self::assertSame($bag, $bag->set('empty', []));
        self::assertFalse($bag->has('empty'));
    }

    public function testScalarMessagesAndNumericTypesAreNormalized(): void
    {
        $messages = [
            'warning' => 'Check settings',
            10 => ['Numeric key'],
        ];
        $bag = new FlashBag($messages);

        self::assertSame(['Check settings'], $bag->peek('warning'));
        self::assertSame(['Numeric key'], $bag->peek('10'));
        self::assertSame([
            'warning' => ['Check settings'],
            10 => ['Numeric key'],
        ], $messages);
    }

    public function testAllConsumesEveryMessageAndPeekAllDoesNot(): void
    {
        $messages = ['success' => ['Saved'], 'error' => ['Failed']];
        $bag = new FlashBag($messages);

        self::assertSame($messages, $bag->peekAll());
        self::assertSame(['success' => ['Saved'], 'error' => ['Failed']], $bag->all());
        self::assertSame([], $messages);
        self::assertSame([], $bag->peekAll());
    }

    public function testClearCanRemoveOneTypeOrEverything(): void
    {
        $messages = ['success' => ['Saved'], 'error' => ['Failed']];
        $bag = new FlashBag($messages);

        self::assertSame($bag, $bag->clear('success'));
        self::assertSame(['error' => ['Failed']], $bag->peekAll());
        self::assertSame($bag, $bag->clear());
        self::assertSame([], $messages);
    }

    public function testCountAndIteratorExposeCurrentMessages(): void
    {
        $messages = ['success' => ['Saved'], 'error' => ['Failed']];
        $bag = new FlashBag($messages);

        self::assertSame(2, count($bag));
        self::assertSame($bag->peekAll(), iterator_to_array($bag->getIterator()));
    }

    public function testCreateBuildsAnIndependentFlashBag(): void
    {
        $bag = FlashBag::create(['notice' => 'Ready']);

        self::assertSame(['Ready'], $bag->peek('notice'));
        $bag->add('notice', 'Set');

        self::assertSame(['Ready', 'Set'], $bag->get('notice'));
    }

    public function testItThrowsWhenTheReferencedFlashDataBecomesCorrupt(): void
    {
        $messages = ['notice' => ['Ready']];
        $bag = new FlashBag($messages, 'flash');
        $messages = 'corrupt';

        $this->expectException(CorruptSessionDataException::class);
        $this->expectExceptionMessage('flash');

        $bag->peekAll();
    }
}
