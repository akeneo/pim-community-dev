<?php

namespace Specification\Akeneo\Platform\Bundle\AuthenticationBundle\Sso;

use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\OneLoginAuthFactory;
use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\SamlToken;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Certificate;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Code;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Configuration;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\EntityId;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\IdentityProvider;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\IsEnabled;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\Repository;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\ServiceProvider;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Url;
use Hslavich\OneloginSamlBundle\Security\Authentication\Token\SamlTokenInterface;
use OneLogin\Saml2\Auth;
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
