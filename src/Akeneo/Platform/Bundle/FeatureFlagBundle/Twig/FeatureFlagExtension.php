<?php

namespace Akeneo\Platform\Bundle\FeatureFlagBundle\Twig;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FeatureFlagExtension extends AbstractExtension
{
    public function __construct(private readonly FeatureFlags $featureFlags)
    {
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('feature_is_enabled', [$this, 'isEnabled']),
        ];
    }

    public function isEnabled(string $feature): bool
    {
        return $this->featureFlags->isEnabled($feature);
    }
}
