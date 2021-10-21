<?php

declare(strict_types=1);

namespace Akeneo\Test\Common;

use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;

/**
 * A fake for CursorInterface. Useful to mock method responses that return a CursorInterface (like ProductQueryBuilder)
 *  Usage: $cursorToFake = new FakeCursor([$product1, $product2, ...]);
 */
class FakeCursor extends \ArrayIterator implements CursorInterface
{
}
