<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\UIBundle\Provider\ContentSecurityPolicy;

use Akeneo\Platform\Bundle\UIBundle\EventListener\ScriptNonceGenerator;

final class AkeneoContentSecurityPolicyProvider implements ContentSecurityPolicyProviderInterface
{
    public function __construct(private readonly ScriptNonceGenerator $nonceGenerator)
    {
    }

    public function getContentSecurityPolicy(): array
    {
        $generatedNonce = $this->nonceGenerator->getGeneratedNonce();

        return [
            'default-src' => ["'self'", "'unsafe-inline'"],
            'script-src' => ["'self'", "'unsafe-eval'", sprintf("'nonce-%s'", $generatedNonce)],
            'img-src' => ["'self'", "data:"],
            'frame-src' => ["*"],
            'font-src' => ["'self'", "data:"],
            'connect-src' => ["'self'", "updates.akeneo.com"],
            'style-src' => ["'self'", "'unsafe-inline'"],
        ];
    }
}
