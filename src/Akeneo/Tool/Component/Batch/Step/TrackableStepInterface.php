<?php

namespace Akeneo\Tool\Component\Batch\Step;

interface TrackableStepInterface
{
    public function isTrackable(): bool;
}
