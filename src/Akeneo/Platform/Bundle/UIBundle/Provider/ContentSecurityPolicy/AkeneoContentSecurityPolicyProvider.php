<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\UIBundle\Provider\ContentSecurityPolicy;

use Akeneo\Platform\Bundle\UIBundle\EventListener\ScriptNonceGenerator;

final class AkeneoContentSecurityPolicyProvider implements ContentSecurityPolicyProviderInterface
{
    private ScriptNonceGenerator $nonceGenerator;

    public function __construct(ScriptNonceGenerator $nonceGenerator)
    {
        $this->nonceGenerator = $nonceGenerator;
    }

    public function getContentSecurityPolicy(): array
    {
        $generatedNonce = $this->nonceGenerator->getGeneratedNonce();

        return [
            'default-src' => ["'self'", "*.akeneo.com", "'unsafe-inline'"],
            'script-src' => ["'self'", "'unsafe-eval'", sprintf("'nonce-%s'", $generatedNonce)],
            'img-src' => ["'self'", "data:"],
            'frame-src' => ["*"],
            'font-src' => ["'self'", "data:"],
            'connect-src'=> ["'self'", "*.akeneo.com"],
        ];
    }
}
