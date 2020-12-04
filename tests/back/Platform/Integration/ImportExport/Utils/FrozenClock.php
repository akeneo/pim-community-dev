<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\ImportExport\Utils;

use Akeneo\Platform\Bundle\ImportExportBundle\Query\ClockInterface;

class FrozenClock implements ClockInterface
{
    /** @var \DateTimeInterface */
    private $dateTime;

    public function setDateTime(\DateTimeInterface $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    public function now(): \DateTimeInterface
    {
        if (!$this->dateTime) {
            throw new \Exception('Frozen clock is not initialized');
        }

        return $this->dateTime;
    }
}
