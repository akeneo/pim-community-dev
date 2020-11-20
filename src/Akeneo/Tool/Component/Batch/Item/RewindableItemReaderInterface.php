<?php
declare(strict_types=1);

namespace Akeneo\Tool\Component\Batch\Item;

interface RewindableItemReaderInterface
{
    public function rewind(): void;
}
