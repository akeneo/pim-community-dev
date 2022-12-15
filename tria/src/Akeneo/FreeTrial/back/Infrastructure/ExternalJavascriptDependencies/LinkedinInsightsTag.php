<?php

declare(strict_types=1);

namespace Akeneo\FreeTrial\Infrastructure\ExternalJavascriptDependencies;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Platform\Bundle\UIBundle\Provider\ContentSecurityPolicy\ContentSecurityPolicyProviderInterface;

final class LinkedinInsightsTag implements ContentSecurityPolicyProviderInterface
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
            'script-src' => ["snap.licdn.com"],
            'img-src' => ["*.ads.linkedin.com", "p.adsymptotic.com", "sjs.bizographics.com"],
            'connect-src' => ["cdn.linkedin.oribi.io", "gw.linkedin.oribi.io", "*.ads.linkedin.com"],
        ];
    }
}
