<?php
declare(strict_types=1);

namespace Akeneo\Tool\Component\Batch\Item;

use Akeneo\Tool\Component\Batch\Job\JobProgress\ItemStepState;
use Akeneo\Tool\Component\Batch\Job\JobProgress\ItemWriterState;

interface PausableItemWriterInterface
{
    public function getState(): ItemWriterState;
}
