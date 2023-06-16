<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Tool\Component\Batch\spec\Item;

use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\PausableReaderInterface;

class FakeReader implements ItemReaderInterface, PausableReaderInterface
{
    public function read(): mixed
    {
        return null;
    }

    public function getState(): array
    {
        return [];
    }
}
