<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Model\Record\Value;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EmptyData implements ValueDataInterface
{
    /**
     * @return null
     */
    public function normalize()
    {
        return null;
    }

    public static function createFromNormalize($normalizedData): ValueDataInterface
    {
        return new self();
    }
}
