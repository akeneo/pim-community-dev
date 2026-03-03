<?php

namespace Akeneo\Platform\Bundle\FeatureFlagBundle\Configuration;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;

class OnlyFreeTrialFeatureFlag implements FeatureFlag
{
    public function __construct(private string $edition)
    {
    }

    public function isEnabled(?string $feature = null): bool
    {
        return $this->edition === 'pim_trial_instance';
    }
}
