<?php

namespace Akeneo\Tool\Component\Connector\Step;

interface TrackableTaskletInterface
{
    public function isTrackable(): bool;
}
