<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Bundle\UIBundle\Provider\ContentSecurityPolicy;

use Akeneo\Platform\Bundle\UIBundle\Provider\ContentSecurityPolicy\ContentSecurityPolicyProviderInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ContentSecurityPolicyProviderSpec extends ObjectBehavior
{
    function it_returns_the_global_policy(
        ContentSecurityPolicyProviderInterface $provider1,
        ContentSecurityPolicyProviderInterface $provider2,
        ContentSecurityPolicyProviderInterface $provider3
    ) {
        $this->beConstructedWith([$provider1, $provider2, $provider3]);

        $provider1->getContentSecurityPolicy()->willReturn([
            'default-src' => ["'self'", "'unsafe-inline'"],
            'script-src' => ["'self'", "'unsafe-eval'", "'nonce-123456'"],
            'img-src' => ["'self'", "data:"],
            'font-src' => ["'self'", "data:"],
        ]);

        $provider2->getContentSecurityPolicy()->willReturn([
            'default-src' => ["'self'", "mywebsite.com", "'unsafe-inline'"],
            'script-src' => ["'nonce-78910'", "'unsafe-eval'"],
            'img-src' => [],
            'frame-src' => ["*", "mywebsite.com"],
        ]);

        $provider3->getContentSecurityPolicy()->willReturn([
            'connect-src'=> ["'self'"],
        ]);

        $this->getPolicy()->shouldReturn("default-src 'self' 'unsafe-inline' mywebsite.com; script-src 'self' 'unsafe-eval' 'nonce-123456' 'nonce-78910'; img-src 'self' data:; font-src 'self' data:; frame-src * mywebsite.com; connect-src 'self'");
    }
}
