<?php

declare(strict_types=1);

namespace Akeneo\FreeTrial\Infrastructure\ExternalJavascriptDependencies;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Platform\Bundle\UIBundle\Provider\ContentSecurityPolicy\ContentSecurityPolicyProviderInterface;

final class RedditPixel implements ContentSecurityPolicyProviderInterface
{
    public function __construct(
        private FeatureFlags $featureFlags
    ) {
    }

    public function getContentSecurityPolicy(): array
    {
        if (!$this->featureFlags->isEnabled('free_trial')) {
            return [];
        }

        return [
            'script-src' => ["alb.reddit.com", "www.redditstatic.com"],
            'img-src' => ["alb.reddit.com"],
        ];
    }
}
