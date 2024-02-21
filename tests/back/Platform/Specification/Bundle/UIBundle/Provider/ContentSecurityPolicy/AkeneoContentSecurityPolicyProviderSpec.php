<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Bundle\UIBundle\Provider\ContentSecurityPolicy;

use Akeneo\Platform\Bundle\UIBundle\EventListener\ScriptNonceGenerator;
use PhpSpec\ObjectBehavior;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AkeneoContentSecurityPolicyProviderSpec extends ObjectBehavior
{
    public function it_returns_the_akeneo_policy(
        ScriptNonceGenerator $nonceGenerator,
    ) {
        $nonceGenerator->getGeneratedNonce()->shouldBeCalled()
            ->willReturn('thisisarandomhash');
        $this->beConstructedWith($nonceGenerator, 'trusted-domain.com');

        $this->getContentSecurityPolicy()->shouldReturn([
            'default-src' =>
                [
                    "'self'",
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
                    "updates.akeneo.com",
                ],
            'style-src' =>
                [
                    "'self'",
                    "'unsafe-inline'",
                ],
        ]);
    }
}
