<?php

namespace Akeneo\Platform\Bundle\FeatureFlagBundle\Configuration;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;

class OnlyGrowthAndSerenityFeatureFlag implements FeatureFlag
{
    private const EDITIONS = [
        'serenity_instance',
        'growth_edition_instance',
    ];

    public function __construct(private readonly string $edition)
    {
    }

    public function isEnabled(?string $feature = null): bool
    {
        return \in_array($this->edition, self::EDITIONS);
    }
}
