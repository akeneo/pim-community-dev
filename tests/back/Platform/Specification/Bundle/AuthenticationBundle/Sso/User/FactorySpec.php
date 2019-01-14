<?php

namespace Specification\Akeneo\Platform\Bundle\AuthenticationBundle\Sso\User;

use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\User\Factory;
use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\User\UnknownUserException;
use Hslavich\OneloginSamlBundle\Security\Authentication\Token\SamlToken;
use Hslavich\OneloginSamlBundle\Security\User\SamlUserFactoryInterface;
use PhpSpec\ObjectBehavior;

class FactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Factory::class);
    }

    function it_is_a_saml_user_factory()
    {
        $this->shouldImplement(SamlUserFactoryInterface::class);
    }

    function it_throws_an_exception_if_the_provisioning_is_disabled()
    {
        $token = new SamlToken();
        $token->setUser('michel');

        $this->shouldThrow(
            new UnknownUserException(
                'michel',
                'The user provisioning is disabled and the user "michel" does not exist.'
            )
        )->during('createUser', [$token]);
    }

    // TODO AOB-340
//    function it_creates_a_user_if_the_provisioning_is_enabled()
//    {
//
//    }
}
