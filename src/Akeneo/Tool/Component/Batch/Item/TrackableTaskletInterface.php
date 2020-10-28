<?php

namespace Akeneo\Tool\Component\Batch\Item;

interface TrackableTaskletInterface
{
    // TODO: A better name could be totalItems(), what do you think ?
    public function count(): int;
}
