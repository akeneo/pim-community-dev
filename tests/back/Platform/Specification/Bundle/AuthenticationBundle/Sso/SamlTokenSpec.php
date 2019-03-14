<?php

namespace Specification\Akeneo\Platform\Bundle\AuthenticationBundle\Sso;

use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\SamlToken;
use Hslavich\OneloginSamlBundle\Security\Authentication\Token\SamlTokenInterface;
use PhpSpec\ObjectBehavior;

class SamlTokenSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(SamlToken::class);
    }

    function it_is_a_saml_token()
    {
        $this->shouldImplement(SamlTokenInterface::class);
    }

    function it_returns_the_firewall_provider_key()
    {
        $this->getProviderKey()->shouldReturn('sso');
    }
}
