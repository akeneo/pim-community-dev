<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Query;

interface ClockInterface
{
    public function now(): \DateTimeInterface;
}
