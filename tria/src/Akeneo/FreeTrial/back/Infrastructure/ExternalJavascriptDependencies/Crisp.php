<?php


namespace Akeneo\FreeTrial\Infrastructure\ExternalJavascriptDependencies;

use Akeneo\Platform\Bundle\UIBundle\EventListener\ScriptNonceGenerator;
use Akeneo\Platform\Bundle\UIBundle\Provider\ContentSecurityPolicy\ContentSecurityPolicyProviderInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\ExternalDependencyProviderInterface;

final class Crisp implements ExternalDependencyProviderInterface, ContentSecurityPolicyProviderInterface
{
    // @todo replace by a proper ID, may be one different by Symfony envs (prod, dev) ?
    private const CRISP_WEBSITE_ID = 'fbbf32c2-0cb1-4142-a8d4-272be589e1f8';

    private ScriptNonceGenerator $nonceGenerator;

    public function __construct(ScriptNonceGenerator $nonceGenerator)
    {
        $this->nonceGenerator = $nonceGenerator;
    }

    /**
     * @see https://help.crisp.chat/en/article/how-to-adjust-my-csp-policy-for-crisp-content-security-policy-bs2jjq/
     */
    public function getContentSecurityPolicy(): array
    {
        return [
            'script-src' => ["*.crisp.chat"],
            'style-src' => ["'self'", "*.crisp.chat", "data:", "'unsafe-inline'"],
            'img-src' => ["*.crisp.chat", "data:"],
            'frame-src' => ["*.crisp.chat"],
            'font-src' => ["*.crisp.chat"],
            'connect-src' => ["*.crisp.chat", "wss://*.crisp.chat"],
        ];
    }

    public function getScript(): string
    {
        $nonce = $this->nonceGenerator->getGeneratedNonce();

        return sprintf(
            '<script type="text/javascript" nonce="%s">window.$crisp=[];window.CRISP_WEBSITE_ID="%s";(function(){d=document;s=d.createElement("script");s.src="https://client.crisp.chat/l.js";s.async=1;d.getElementsByTagName("head")[0].appendChild(s);})();</script>',
            $nonce,
            self::CRISP_WEBSITE_ID
        );
    }
}
