<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\UIBundle\Provider\ContentSecurityPolicy;

use Akeneo\Platform\Bundle\UIBundle\EventListener\ScriptNonceGenerator;

final class AkeneoContentSecurityPolicyProvider implements ContentSecurityPolicyProviderInterface
{
    public function __construct(
        private readonly ScriptNonceGenerator $nonceGenerator,
        private readonly string $trustedDomain
    ) {
    }

    public function getContentSecurityPolicy(): array
    {
        $generatedNonce = $this->nonceGenerator->getGeneratedNonce();
        $trustedSubDomains = sprintf('*.%s', $this->trustedDomain);

        return [
            'default-src' => ["'self'", $trustedSubDomains, "'unsafe-inline'"],
            'script-src' => ["'self'", "'unsafe-eval'", sprintf("'nonce-%s'", $generatedNonce)],
            'img-src' => ["'self'", "data:", $trustedSubDomains,],
            'frame-src' => ["*"],
            'font-src' => ["'self'", "data:"],
            'connect-src' => ["'self'", $trustedSubDomains],
        ];
    }
}
