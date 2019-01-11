<?php

namespace Specification\Akeneo\Platform\Bundle\AuthenticationBundle\Sso;

use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\OneLoginAuthFactory;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Certificate;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Code;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Configuration;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\EntityId;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\IdentityProvider;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\IsEnabled;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\Repository;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\ServiceProvider;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Url;
use OneLogin\Saml2\Auth;
use PhpSpec\ObjectBehavior;

class OneLoginAuthFactorySpec extends ObjectBehavior
{
    function let(Repository $configRepository)
    {
        $this->beConstructedWith(
            $configRepository,
            [
                'idp' => [
                    'entityId' => 'http://idp-server-url/simplesaml/saml2/idp/metadata.php',
                    'singleSignOnService' => [
                        'url' => 'http://idp-server-url/simplesaml/saml2/idp/SSOService.php',
                        'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                    ],
                    'singleLogoutService' => [
                        'url' => 'http://idp-server-url/simplesaml/saml2/idp/SingleLogoutService.php',
                        'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                    ],
                    'x509cert' => 'SSO_IDP_SERVER_PUBLIC_KEY_VALUE',
                ],
                'sp' => [
                    'entityId' => 'https://my.pim.com',
                    'assertionConsumerService' => [
                        'url' => 'https://my.pim.com/saml/acs',
                        'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                    ],
                    'singleLogoutService' => [
                        'url' => 'https://my.pim.com/saml/logout',
                        'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                    ],
                    'x509cert' => 'SSO_SP_CLIENT_PUBLIC_KEY_VALUE',
                    'privateKey' => 'SSO_SP_CLIENT_PRIVATE_KEY_VALUE',
                ],
                'security' => [
                    'authnRequestsSigned' => true
                ],
            ]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(OneLoginAuthFactory::class);
    }

    function it_instantiates_auth_with_overriden_config($configRepository)
    {
        $userDefinedConfig = new Configuration(
            new Code('authentication_sso'),
            new IsEnabled(true),
            new IdentityProvider(
                new EntityId('https://idp.jambon.com'),
                new Url('https://idp.jambon.com/signon'),
                new Url('https://idp.jambon.com/logout'),
                new Certificate('idp_certificate')
            ),
            new ServiceProvider(
                new EntityId('https://my.pim.com'),
                new Certificate('sp_certificate'),
                new Certificate('sp_private_key')
            )
        );
        $configRepository->find('authentication_sso')->willReturn($userDefinedConfig);

        $expectedAuth = new Auth(
            [
                'idp' => [
                    'entityId'            => 'https://idp.jambon.com',
                    'singleSignOnService' => [
                        'url'     => 'https://idp.jambon.com/signon',
                        'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                    ],
                    'singleLogoutService' => [
                        'url'     => 'https://idp.jambon.com/logout',
                        'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                    ],
                    'x509cert' => 'idp_certificate',
                ],
                'sp' => [
                    'entityId'                 => 'https://my.pim.com',
                    'assertionConsumerService' => [
                        'url'     => 'https://my.pim.com/saml/acs',
                        'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                    ],
                    'singleLogoutService' => [
                        'url'     => 'https://my.pim.com/saml/logout',
                        'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                    ],
                    'x509cert'   => 'sp_certificate',
                    'privateKey' => 'sp_private_key',
                ],
                'security' => [
                    'authnRequestsSigned' => true,
                ],
            ]
        );

        $this->create()->shouldBeLike($expectedAuth);
    }
}
