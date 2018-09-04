<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Model\Record\Value;

interface ValueDataInterface
{
    public function normalize();

    public static function createFromNormalize($normalizedData): ValueDataInterface;
}
