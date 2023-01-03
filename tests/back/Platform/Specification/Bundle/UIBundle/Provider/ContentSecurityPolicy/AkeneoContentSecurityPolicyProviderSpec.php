<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Bundle\UIBundle\Provider\ContentSecurityPolicy;

use Akeneo\Platform\Bundle\UIBundle\EventListener\ScriptNonceGenerator;
use PhpSpec\ObjectBehavior;

class AkeneoContentSecurityPolicyProviderSpec extends ObjectBehavior
{
    function it_returns_the_akeneo_policy(
        ScriptNonceGenerator $nonceGenerator,
    ) {
        $nonceGenerator->getGeneratedNonce()->shouldBeCalled()
            ->willReturn('thisisarandomhash');
        $this->beConstructedWith($nonceGenerator, 'trusted-domain.com');

        $this->getContentSecurityPolicy()->shouldReturn([
            'default-src' =>
                [
                    "'self'",
                    '*.trusted-domain.com',
                    "'unsafe-inline'",
                ],
            'script-src' =>
                [
                    "'self'",
                    "'unsafe-eval'",
                    "'nonce-thisisarandomhash'",
                ],
            'img-src' =>
                [
                    "'self'",
                    'data:',
                    '*.trusted-domain.com',
                ],
            'frame-src' =>
                [
                    '*',
                ],
            'font-src' =>
                [
                    "'self'",
                    'data:',
                ],
            'connect-src' =>
                [
                    "'self'",
                    '*.trusted-domain.com',
                ],
        ]);
    }
}

