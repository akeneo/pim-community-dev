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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\POC;

use Akeneo\Platform\Bundle\UIBundle\EventListener\ScriptNonceGenerator;

final class AkeneoContentSecurityPolicy implements ContentSecurityPolicyProviderInterface
{
    private string $generatedNonce;

    public function __construct(ScriptNonceGenerator $nonceGenerator)
    {
        $this->generatedNonce = $nonceGenerator->getGeneratedNonce();
    }

    public function getContentSecurityPolicy(): array
    {
        return [
            'default-src' => ["'self'", "*.akeneo.com", "'unsafe-inline'"],
            'script-src' => ["'self'", "'unsafe-eval'", sprintf("'nonce-%s'", $this->generatedNonce)],
            'img-src' => ["'self'", "data:"],
            'frame-src' => ["*"],
            'font-src' => ["'self'", "data:"],
            'connect-src'=> ["'self'", "*.akeneo.com"],
        ];
    }
}
