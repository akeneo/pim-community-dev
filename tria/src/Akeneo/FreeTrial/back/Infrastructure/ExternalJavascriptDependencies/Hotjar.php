<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\FreeTrial\Infrastructure\ExternalJavascriptDependencies;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Platform\Bundle\UIBundle\EventListener\ScriptNonceGenerator;
use Akeneo\Platform\Bundle\UIBundle\Provider\ContentSecurityPolicy\ContentSecurityPolicyProviderInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\ExternalDependencyProviderInterface;

final class Hotjar implements ExternalDependencyProviderInterface, ContentSecurityPolicyProviderInterface
{
    public function __construct(
        private ScriptNonceGenerator $nonceGenerator,
        private string $HotjarId,
        private FeatureFlags $featureFlags
    ) {
    }

    public function getScript(): ?string
    {
        if (!$this->featureFlags->isEnabled('free_trial')) {
            return null;
        }

        $javascript = <<<JS
<script type="text/javascript" nonce="%s">
    (function(h,o,t,j,a,r){
        h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
        h._hjSettings={hjid:%s,hjsv:6};
        a=o.getElementsByTagName('head')[0];
        r=o.createElement('script');r.async=1;
        r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
        a.appendChild(r);
    })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
</script>
JS;

        $nonce = $this->nonceGenerator->getGeneratedNonce();

        return sprintf($javascript, $nonce, $this->HotjarId);
    }

    /**
     * https://help.hotjar.com/hc/en-us/articles/115011640307
     */
    public function getContentSecurityPolicy(): array
    {
        if (!$this->featureFlags->isEnabled('free_trial')) {
            return [];
        }

        return [
            'script-src'  => [
                "http://*.hotjar.com",
                "https://*.hotjar.com",
                "http://*.hotjar.io",
                "https://*.hotjar.io",
                "'unsafe-inline'",
            ],
            'img-src'     => [
                "http://*.hotjar.com",
                "https://*.hotjar.com",
                "http://*.hotjar.io",
                "https://*.hotjar.io",
            ],
            'font-src'    => [
                "http://*.hotjar.com",
                "https://*.hotjar.com",
                "http://*.hotjar.io",
                "https://*.hotjar.io"
            ],
            'connect-src' => [
                "http://*.hotjar.com:*",
                "https://*.hotjar.com:*",
                "http://*.hotjar.io",
                "https://*.hotjar.io",
                "wss://*.hotjar.com",
            ],
            'frame-src' => [
                "https://*.hotjar.com",
                "http://*.hotjar.io",
                "https://*.hotjar.io",
            ]
        ];
    }
}
