<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Batch\Job;

interface VisibleJobInterface
{
    public function isVisible(): bool;
}
