<?php

namespace Akeneo\Tool\Component\Batch\Job;

interface StoppableJobInterface
{
    public function isStoppable(): bool;
}
