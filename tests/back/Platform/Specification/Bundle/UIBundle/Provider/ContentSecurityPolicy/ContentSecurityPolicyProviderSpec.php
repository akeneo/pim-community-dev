<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Bundle\UIBundle\Provider\ContentSecurityPolicy;

use Akeneo\Platform\Bundle\UIBundle\Provider\ContentSecurityPolicy\ContentSecurityPolicyProviderInterface;
use PhpSpec\ObjectBehavior;

class ContentSecurityPolicyProviderSpec extends ObjectBehavior
{
    function it_returns_the_global_policy(
        ContentSecurityPolicyProviderInterface $provider1,
        ContentSecurityPolicyProviderInterface $provider2,
        ContentSecurityPolicyProviderInterface $provider3
    ) {
        $this->beConstructedWith([$provider1, $provider2, $provider3]);

        $provider1->getContentSecurityPolicy()->willReturn([
            'default-src' => ["'self'", "*.akeneo.com", "'unsafe-inline'"],
            'script-src' => ["'self'", "'unsafe-eval'", "'nonce-123456'"],
            'img-src' => ["'self'", "data:"],
            'font-src' => ["'self'", "data:"],
        ]);

        $provider2->getContentSecurityPolicy()->willReturn([
            'default-src' => ["'self'", "*.mywebsite.com", "'unsafe-inline'"],
            'script-src' => ["'nonce-78910'", "'unsafe-eval'"],
            'img-src' => [],
            'frame-src' => ["*", "*.akeneo.com"],
        ]);

        $provider3->getContentSecurityPolicy()->willReturn([
            'connect-src'=> ["'self'", "*.akeneo.com"],
        ]);

        $this->getPolicy()->shouldReturn("default-src 'self' *.akeneo.com 'unsafe-inline' *.mywebsite.com; script-src 'self' 'unsafe-eval' 'nonce-123456' 'nonce-78910'; img-src 'self' data:; font-src 'self' data:; frame-src * *.akeneo.com; connect-src 'self' *.akeneo.com");
    }
}

