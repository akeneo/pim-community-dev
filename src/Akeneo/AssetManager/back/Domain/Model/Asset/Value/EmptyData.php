<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Domain\Model\Asset\Value;

use Webmozart\Assert\Assert;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EmptyData implements ValueDataInterface
{
    private function __construct()
    {
    }

    /**
     * @return null
     */
    public function normalize()
    {
        return null;
    }

    public static function createFromNormalize($normalizedData): ValueDataInterface
    {
        Assert::null($normalizedData, 'Normalized data should be null');

        return new self();
    }

    public static function create(): ValueDataInterface
    {
        return new self();
    }
}
