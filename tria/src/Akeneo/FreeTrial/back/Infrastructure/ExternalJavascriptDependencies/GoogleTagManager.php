<?php

declare(strict_types=1);

namespace Akeneo\FreeTrial\Infrastructure\ExternalJavascriptDependencies;

use Akeneo\Platform\Bundle\UIBundle\EventListener\ScriptNonceGenerator;
use Akeneo\Platform\Bundle\UIBundle\Provider\ContentSecurityPolicy\ContentSecurityPolicyProviderInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\ExternalDependencyProviderInterface;

final class GoogleTagManager implements ExternalDependencyProviderInterface, ContentSecurityPolicyProviderInterface
{
    private ScriptNonceGenerator $nonceGenerator;
    private string $googleTagManagerId;

    public function __construct(ScriptNonceGenerator $nonceGenerator, string $googleTagManagerId)
    {
        $this->nonceGenerator = $nonceGenerator;
        $this->googleTagManagerId = $googleTagManagerId;
    }

    public function getScript(): string
    {
        $nonce = $this->nonceGenerator->getGeneratedNonce();

        $javascript = <<<JS
<script nonce="$nonce">
  window.dataLayer = window.dataLayer||[];
  dataLayer.push({'pim-nonce': '$nonce'});
</script>
<!-- Google Tag Manager -->
<script nonce="$nonce">(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;var n=d.querySelector('[nonce]');
n&&j.setAttribute('nonce',n.nonce||n.getAttribute('nonce'));f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','%s');</script>
<!-- End Google Tag Manager -->
JS;

        return sprintf($javascript, $this->googleTagManagerId);
    }

    /**
     * https://developers.heap.io/docs/web#content-security-policy-csp
     */
    public function getContentSecurityPolicy(): array
    {
        return [
            'script-src'  => ["https://www.googletagmanager.com", "'unsafe-inline'"],
            'img-src'     => ["www.googletagmanager.com"],
            'style-src'   => ["https://www.googletagmanager.com"],
            'connect-src' => ["https://www.google-analytics.com"]
        ];
    }
}
