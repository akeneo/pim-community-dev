<?php

declare(strict_types=1);

namespace Akeneo\FreeTrial\Infrastructure\ExternalJavascriptDependencies;

use Akeneo\Platform\Bundle\UIBundle\EventListener\ScriptNonceGenerator;
use Akeneo\Platform\Bundle\UIBundle\Provider\ContentSecurityPolicy\ContentSecurityPolicyProviderInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\ExternalDependencyProviderInterface;

final class GoogleAnalytics implements ExternalDependencyProviderInterface, ContentSecurityPolicyProviderInterface
{
    private ScriptNonceGenerator $nonceGenerator;
    private string $googleAnalyticsId;

    public function __construct(ScriptNonceGenerator $nonceGenerator, string $googleAnalyticsId)
    {
        $this->nonceGenerator = $nonceGenerator;
        $this->googleAnalyticsId = $googleAnalyticsId;
    }

    public function getScript(): string
    {
        $nonce = $this->nonceGenerator->getGeneratedNonce();

        $javascript = <<<JS
<!-- Global site tag (gtag.js) - Google Analytics -->
<script nonce="$nonce" async src="https://www.googletagmanager.com/gtag/js?id=%s"></script>
<script nonce="$nonce">
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '%s');
</script>
JS;

        return sprintf($javascript, $this->googleAnalyticsId, $this->googleAnalyticsId);
    }

    /**
     * @see https://developers.google.com/tag-manager/web/csp
     * @see https://rapidsec.com/csp-packages/google_analytics
     */
    public function getContentSecurityPolicy(): array
    {
        return [
            'script-src' => ["https://www.googletagmanager.com", "'unsafe-inline'"],
            'img-src' => ["www.googletagmanager.com", "www.google-analytics.com", "ssl.google-analytics.com", "www.google.com", "analytics.google.com"],
            'style-src' => ["https://www.googletagmanager.com"],
            'connect-src' => ["https://www.google-analytics.com", "*.g.doubleclick.net"],
            'frame-src' => ["*.g.doubleclick.net"],
        ];
    }
}
