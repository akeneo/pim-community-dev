<?php
declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso;

use Hslavich\OneloginSamlBundle\Security\Authentication\Token\SamlTokenFactoryInterface;
use Hslavich\OneloginSamlBundle\Security\Authentication\Token\SamlTokenInterface;

/*
 * Override the SamlTokenFactory in order to instanciate a custom SamlToken that expose a public method that is required
 * in order to properly generate logout url.
 */

class SamlTokenFactory implements SamlTokenFactoryInterface
{

    /**
     * Creates a new SAML Token object.
     *
     * @param mixed $user
     * @param array $attributes
     * @param array $roles
     *
     * @return SamlTokenInterface
     */
    public function createToken($user, array $attributes, array $roles)
    {
        $token = new SamlToken($roles);
        $token->setUser($user);
        $token->setAttributes($attributes);

        return $token;
    }
}
