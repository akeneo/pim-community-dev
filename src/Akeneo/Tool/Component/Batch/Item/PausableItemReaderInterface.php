<?php
declare(strict_types=1);

namespace Akeneo\Tool\Component\Batch\Item;

interface PausableItemReaderInterface
{
    public function rewindToState(array $state): void;

    public function getState(): array;
}
