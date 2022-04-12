<?php

namespace Akeneo\Platform\Bundle\FeatureFlagBundle\Configuration;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;

class OnlyFlexibilityOnPremiseFeatureFlag implements FeatureFlag
{
    private const SAAS_EDITIONS = [
        'SERENITY_EDITION',
        'GROWTH_EDITION',
        'TRIAL_EDITION',
    ];

    public function __construct(
        private string $edition
    ) {
    }

    public function isEnabled(): bool
    {
        return !in_array($this->edition, self::SAAS_EDITIONS);
    }
}
