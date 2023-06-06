<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Tool\Component\Batch\spec\Item;

use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Item\PausableWriterInterface;

class FakeWriter implements ItemWriterInterface, PausableWriterInterface
{
    public function write(array $items): void
    {
        return;
    }

    public function getState(): array
    {
        return [];
    }
}
