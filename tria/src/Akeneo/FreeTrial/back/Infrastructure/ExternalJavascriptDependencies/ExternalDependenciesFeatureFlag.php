<?php

declare(strict_types=1);

namespace Akeneo\FreeTrial\Infrastructure\ExternalJavascriptDependencies;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;

final class ExternalDependenciesFeatureFlag
{
    private FeatureFlag $freeTrialFeature;

    public function __construct(FeatureFlag $freeTrialFeature)
    {
        $this->freeTrialFeature = $freeTrialFeature;
    }

    public function isEnabled(): bool
    {
        return $this->freeTrialFeature->isEnabled();
    }
}
