<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\UI;

use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Log\CreateArchive;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Application\CreateOrUpdateConfiguration;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Application\CreateOrUpdateConfigurationHandler;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Certificate;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Code;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Configuration;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\EntityId;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\IdentityProvider;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\IsEnabled;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\ConfigurationNotFound;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\Repository;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\ServiceProvider;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\ServiceProviderDefaultConfiguration;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Url;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ControllerSpec extends ObjectBehavior
{
    const CERTIFICATE = 'MIIDYDCCAkigAwIBAgIJAOGDWOB07tCyMA0GCSqGSIb3DQEBCwUAMEUxCzAJBgNVBAYTAkFVMRMwEQYDVQQIDApTb21lLVN0YXRlMSEwHwYDVQQKDBhJbnRlcm5ldCBXaWRnaXRzIFB0eSBMdGQwHhcNMTgwOTE0MDkzMDEzWhcNMjgwOTEzMDkzMDEzWjBFMQswCQYDVQQGEwJBVTETMBEGA1UECAwKU29tZS1TdGF0ZTEhMB8GA1UECgwYSW50ZXJuZXQgV2lkZ2l0cyBQdHkgTHRkMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA4J5iDNmrQLn4NHVvjTR0Z+xqmW6mYWFP/MxI4D4urwv6J0CLZppxfcSXLYogxrC5U+JxlF7jv9CM6Dpvkc4xBFyCNVIKAwBh/W+fL85m48Fd7Nh1VW+fK8ZBDUKFfuRxK+H/0shU96z2onVB6uYiNxF0+26MwZwjecLIh6st+pEKzd2aUNgB9RYPJWqdxw8R5mZH2EfzjTDKyomAeENcVW6zK9kQP6YNC7T8mYaUus4jhAcC/jV8Iqy7Oc1h+tEQV3rqFLLKezuNZWufoOrzaPoKMOkXgasxtadM1wU9InIpiO6pWPCwNc6TLpmZCcry6yIoveMx5fzMGjgxmmmUrwIDAQABo1MwUTAdBgNVHQ4EFgQUitGGamyDFTInis6Umd+Wc+NoFoMwHwYDVR0jBBgwFoAUitGGamyDFTInis6Umd+Wc+NoFoMwDwYDVR0TAQH/BAUwAwEB/zANBgkqhkiG9w0BAQsFAAOCAQEA05pnfsR6Atm9Wx+fdaFG7DVnrQjLDXRCXqkRJ09ygrpJlIF6YBPXcYA0kidoVlBbkZhWzz1ht5i1BYKzKdWzB2BQZLUnkaM8UotKFjdHu7/7vnM7w/n3S5zx3gtoCMSegp9vk6H2wjsPYfR0mVJOYcFzRY48bdQLV6nJRU3gV+ZikM/u92xArcaTCS6l4YEBCqJWtvlVojc6nwwv262t6NJ8NHRHqV98aoNMO4ltjFIkXa0xtNqYo7pI01kkTlPrignb4djZjCpdwu/lZJTy4FAra4lTdu2j4nn8QxNKDoBIrsNx6b+C767Mtf1f3JSRMAvt/IE4Wjp5IIAeLsTHSA==';

    function let(
        ValidatorInterface $validator,
        NormalizerInterface $normalizer,
        CreateOrUpdateConfigurationHandler $createOrUpdateConfigHandler,
        Repository $repository,
        ServiceProviderDefaultConfiguration $serviceProviderDefaultConfiguration,
        CreateArchive $createArchive,
        PresenterInterface $datePresenter,
        UserContext $userContext
    ) {
        $this->beConstructedWith(
            $validator,
            $normalizer,
            $createOrUpdateConfigHandler,
            $repository,
            $serviceProviderDefaultConfiguration,
            $createArchive,
            $datePresenter,
            $userContext,
            'http://my.akeneopim.com'
        );

        $userContext->getUiLocaleCode()->willReturn('en_US');
        $userContext->getUserTimezone()->willReturn('UTC');

        $datePresenter->present(
            new \DateTime('2028-09-13 09:30:13.000000'),
            [
                'locale'   => 'en_US',
                'timezone' => 'UTC',
            ]
        )->willReturn('09/13/2028');
    }

    function it_saves_configuration($validator, $createOrUpdateConfigHandler)
    {
        $requestContent = json_encode(
            [
                'is_enabled'                    => true,
                'identity_provider_entity_id'   => 'https://idp.jambon.com',
                'identity_provider_sign_on_url' => 'https://idp.jambon.com/signon',
                'identity_provider_logout_url'  => 'https://idp.jambon.com/logout',
                'identity_provider_certificate' => self::CERTIFICATE,
                'service_provider_entity_id'    => 'https://sp.jambon.com',
                'service_provider_certificate'  => self::CERTIFICATE,
                'service_provider_private_key'  => 'private_key',
            ]
        );
        $request = new Request([], [], [], [], [], [], $requestContent);
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $validator->validate(Argument::type(CreateOrUpdateConfiguration::class))
            ->willReturn(new ConstraintViolationList())
        ;

        $createOrUpdateConfigHandler->handle(Argument::type(CreateOrUpdateConfiguration::class))->shouldBeCalled();

        $expectedResponse = new JsonResponse();

        $now = new \DateTime();
        $expectedResponse->setDate($now);

        $actualResponse = $this->saveAction($request);
        $actualResponse->setDate($now);

        $actualResponse->shouldBeLike($expectedResponse);
    }

    function it_returns_normalized_errors_if_configuration_is_invalid(
        $validator,
        $normalizer,
        $createOrUpdateConfigHandler
    ) {
        $requestContent = json_encode(
            [
                'is_enabled'                    => true,
                'identity_provider_entity_id'   => '',
                'identity_provider_sign_on_url' => 'https://idp.jambon.com/signon',
                'identity_provider_logout_url'  => 'https://idp.jambon.com/logout',
                'identity_provider_certificate' => self::CERTIFICATE,
                'service_provider_entity_id'    => 'https://sp.jambon.com',
                'service_provider_certificate'  => self::CERTIFICATE,
                'service_provider_private_key'  => 'private_key',
            ]
        );
        $request = new Request([], [], [], [], [], [], $requestContent);
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $command = new CreateOrUpdateConfiguration(
            'authentication_sso',
            true,
            '',
            'https://idp.jambon.com/signon',
            'https://idp.jambon.com/logout',
            self::CERTIFICATE,
            'https://sp.jambon.com',
            self::CERTIFICATE,
            'private_key'
        );

        $validator->validate(Argument::type(CreateOrUpdateConfiguration::class))
            ->shouldBeCalled()
            ->willReturn(new ConstraintViolationList([
                new ConstraintViolation(
                    'This value should not be blank.',
                    'This value should not be blank.',
                    [],
                    $command,
                    'identityProviderEntityId',
                    ''
                ),
            ]))
        ;

        $normalizedErrors = [
            [
                'global'  => false,
                'message' => 'This value should not be blank.',
                'path'    => 'identity_provider_entity_id',
            ]
        ];

        $normalizer->normalize(Argument::type(ConstraintViolationList::class), 'internal_api')
            ->shouldBeCalled()
            ->willReturn($normalizedErrors)
        ;

        $createOrUpdateConfigHandler->handle(Argument::cetera())->shouldNotBeCalled();

        $expectedResponse = new JsonResponse($normalizedErrors, 400);

        $now = new \DateTime();
        $expectedResponse->setDate($now);

        $actualResponse = $this->saveAction($request);
        $actualResponse->setDate($now);

        $actualResponse->shouldBeLike($expectedResponse);
    }

    function it_gives_an_existing_configuration($repository)
    {
        $config = new Configuration(
            new Code('authentication_sso'),
            new IsEnabled(true),
            new IdentityProvider(
                new EntityId('https://idp.jambon.com'),
                new Url('https://idp.jambon.com/signon'),
                new Url('https://idp.jambon.com/logout'),
                new Certificate(self::CERTIFICATE)
            ),
            new ServiceProvider(
                new EntityId('https://sp.jambon.com'),
                new Certificate(self::CERTIFICATE),
                new Certificate('private_key')
            )
        );

        $repository->find('authentication_sso')->willReturn($config);

        $expectedResponse = new JsonResponse([
            'configuration' => [
                'is_enabled'                    => true,
                'identity_provider_entity_id'   => 'https://idp.jambon.com',
                'identity_provider_sign_on_url' => 'https://idp.jambon.com/signon',
                'identity_provider_logout_url'  => 'https://idp.jambon.com/logout',
                'identity_provider_certificate' => self::CERTIFICATE,
                'service_provider_entity_id'    => 'https://sp.jambon.com',
                'service_provider_certificate'  => self::CERTIFICATE,
                'service_provider_private_key'  => 'private_key',
            ],
            'meta' => [
                'service_provider_certificate_expiration_date' => '09/13/2028',
                'service_provider_certificate_expires_soon'    => false,
                'service_provider_metadata_url'                => 'http://my.akeneopim.com/saml/metadata',
                'service_provider_acs_url'                     => 'http://my.akeneopim.com/saml/acs',
            ],
        ]);

        $now = new \DateTime();
        $expectedResponse->setDate($now);

        $actualResponse = $this->getAction();
        $actualResponse->setDate($now);

        $actualResponse->shouldBeLike($expectedResponse);
    }

    function it_gives_a_default_configuration($repository, $serviceProviderDefaultConfiguration)
    {
        $repository->find('authentication_sso')->willThrow(ConfigurationNotFound::class);

        $serviceProviderConfiguration = new ServiceProvider(
            new EntityId('https://sp.jambon.com/saml/metadata'),
            new Certificate(self::CERTIFICATE),
            new Certificate('default_private_key')
        );

        $serviceProviderDefaultConfiguration->getServiceProvider()->willReturn($serviceProviderConfiguration);

        $expectedResponse = new JsonResponse([
            'configuration' => [
                'is_enabled'                    => false,
                'identity_provider_entity_id'   => '',
                'identity_provider_sign_on_url' => '',
                'identity_provider_logout_url'  => '',
                'identity_provider_certificate' => '',
                'service_provider_entity_id'    => 'https://sp.jambon.com/saml/metadata',
                'service_provider_certificate'  => self::CERTIFICATE,
                'service_provider_private_key'  => 'default_private_key',
            ],
            'meta' => [
                'service_provider_certificate_expiration_date' => '09/13/2028',
                'service_provider_certificate_expires_soon'    => false,
                'service_provider_metadata_url'                => 'http://my.akeneopim.com/saml/metadata',
                'service_provider_acs_url'                     => 'http://my.akeneopim.com/saml/acs',
            ],
        ]);

        $now = new \DateTime();
        $expectedResponse->setDate($now);

        $actualResponse = $this->getAction();
        $actualResponse->setDate($now);

        $actualResponse->shouldBeLike($expectedResponse);
    }
}
