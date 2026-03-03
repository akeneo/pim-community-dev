<?php

namespace Akeneo\Platform\Bundle\FeatureFlagBundle\Configuration;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;

class OnlyFlexibilityOnPremiseFeatureFlag implements FeatureFlag
{
    private const SAAS_EDITIONS = [
        'serenity_instance',
        'growth_edition_instance',
        'pim_trial_instance',
    ];

    public function __construct(
        private string $edition
    ) {
    }

    public function isEnabled(?string $feature = null): bool
    {
        return !in_array($this->edition, self::SAAS_EDITIONS);
    }
}
