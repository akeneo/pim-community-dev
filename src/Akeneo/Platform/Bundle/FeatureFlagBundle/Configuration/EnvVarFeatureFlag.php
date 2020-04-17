<?php

namespace Akeneo\Platform\Bundle\FeatureFlagBundle\Configuration;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;

class EnvVarFeatureFlag implements FeatureFlag
{
    /** @var bool */
    private $envVar;

    public function __construct($envVar)
    {
        $this->envVar = boolval($envVar);
    }

    public function isEnabled(): bool
    {
        return boolval($this->envVar);
    }
}
