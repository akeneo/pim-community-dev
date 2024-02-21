<?php

namespace Akeneo\Tool\Component\Batch\Item;

interface TrackableTaskletInterface
{
    public function isTrackable(): bool;
}
