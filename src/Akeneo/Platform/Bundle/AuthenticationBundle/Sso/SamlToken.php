<?php
declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso;

class SamlToken extends \Hslavich\OneloginSamlBundle\Security\Authentication\Token\SamlToken
{
    /*
     * Provide the firewall key value via the saml token in order to correctly generate the uri of logout.
     * This public method is not in any interface but the logoutUrlGenerator service test if the method exists.
     * https://github.com/symfony/symfony/blob/3.4/src/Symfony/Component/Security/Http/Logout/LogoutUrlGenerator.php#L168
     *
     */
    public function getProviderKey()
    {
        return 'sso';
    }
}
