<?php

declare(strict_types=1);

namespace Akeneo\FreeTrial\Infrastructure\ExternalJavascriptDependencies;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Platform\Bundle\UIBundle\Provider\ContentSecurityPolicy\ContentSecurityPolicyProviderInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\ExternalDependencyProviderInterface;

final class Appcues implements ExternalDependencyProviderInterface, ContentSecurityPolicyProviderInterface
{
    public function __construct(private string $appcuesId, private FeatureFlags $featureFlags)
    {
    }

    public function getScript(): string
    {
        if (!$this->featureFlags->isEnabled('free_trial')) {
            return '';
        }

        return sprintf(
            '<script src="https://fast.appcues.com/%s.js"></script>',
            $this->appcuesId
        );
    }

    /**
     * https://docs.appcues.com/article/234-content-security-policies
     */
    public function getContentSecurityPolicy(): array
    {
        if (!$this->featureFlags->isEnabled('free_trial')) {
            return [];
        }

        return [
            'frame-src'   => ["'self'", "https://*.appcues.com"],
            'style-src'   => ["'self'", "https://*.appcues.com", "https://*.appcues.net", "https://fonts.googleapis.com", "'unsafe-inline'"],
            'script-src'  => ["'self'", "https://*.appcues.com", "https://*.appcues.net", "'unsafe-inline'"],
            'img-src'     => ["'self'", "res.cloudinary.com", "twemoji.maxcdn.com"],
            'connect-src' => ["https://*.appcues.com", "https://*.appcues.net", "wss://*.appcues.net", "wss://*.appcues.com"],
            'font-src'    => ["https://fonts.gstatic.com"]
        ];
    }
}
