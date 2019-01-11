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
    function let(
        ValidatorInterface $validator,
        NormalizerInterface $normalizer,
        CreateOrUpdateConfigurationHandler $createOrUpdateConfigHandler,
        Repository $repository,
        ServiceProviderDefaultConfiguration $serviceProviderDefaultConfiguration,
        CreateArchive $createArchive
    ) {
        $this->beConstructedWith(
            $validator,
            $normalizer,
            $createOrUpdateConfigHandler,
            $repository,
            $serviceProviderDefaultConfiguration,
            $createArchive
        );
    }

    function it_saves_configuration($validator, $createOrUpdateConfigHandler)
    {
        $requestContent = json_encode(
            [
                'is_enabled'                    => true,
                'identity_provider_entity_id'   => 'https://idp.jambon.com',
                'identity_provider_sign_on_url' => 'https://idp.jambon.com/signon',
                'identity_provider_logout_url'  => 'https://idp.jambon.com/logout',
                'identity_provider_certificate' => 'certificate',
                'service_provider_entity_id'    => 'https://sp.jambon.com',
                'service_provider_certificate'  => 'certificate',
                'service_provider_private_key'  => 'private_key',
            ]
        );
        $request = new Request([], [], [], [], [], [], $requestContent);
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $validator->validate(Argument::type(CreateOrUpdateConfiguration::class))
            ->willReturn(new ConstraintViolationList())
        ;

        $createOrUpdateConfigHandler->handle(Argument::type(CreateOrUpdateConfiguration::class))->shouldBeCalled();

        $this->saveAction($request)->shouldBeLike(new JsonResponse());
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
                'identity_provider_certificate' => 'certificate',
                'service_provider_entity_id'    => 'https://sp.jambon.com',
                'service_provider_certificate'  => 'certificate',
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
            'certificate',
            'https://sp.jambon.com',
            'certificate',
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

        $this->saveAction($request)->shouldBeLike(new JsonResponse($normalizedErrors, 400));
    }

    function it_gives_an_existing_configuration($repository, $normalizer)
    {
        $config = new Configuration(
            new Code('authentication_sso'),
            new IsEnabled(true),
            new IdentityProvider(
                new EntityId('https://idp.jambon.com'),
                new Url('https://idp.jambon.com/signon'),
                new Url('https://idp.jambon.com/logout'),
                new Certificate('certificate')
            ),
            new ServiceProvider(
                new EntityId('https://sp.jambon.com'),
                new Certificate('certificate'),
                new Certificate('private_key')
            )
        );

        $repository->find('authentication_sso')->willReturn($config);

        $normalizedConfig = [
            'is_enabled'                    => true,
            'identity_provider_entity_id'   => 'https://idp.jambon.com',
            'identity_provider_sign_on_url' => 'https://idp.jambon.com/signon',
            'identity_provider_logout_url'  => 'https://idp.jambon.com/logout',
            'identity_provider_certificate' => 'certificate',
            'service_provider_entity_id'    => 'https://sp.jambon.com',
            'service_provider_certificate'  => 'certificate',
            'service_provider_private_key'  => 'private_key',
        ];

        $normalizer->normalize($config, 'internal_api')->willReturn($normalizedConfig);

        $this->getAction()->shouldBeLike(new JsonResponse($normalizedConfig));
    }

    function it_gives_a_default_configuration($repository, $serviceProviderDefaultConfiguration)
    {
        $repository->find('authentication_sso')->willThrow(ConfigurationNotFound::class);

        $serviceProviderConfiguration = new ServiceProvider(
            new EntityId('https://sp.jambon.com/saml/metadata'),
            new Certificate('default_certificate'),
            new Certificate('default_private_key')
        );

        $serviceProviderDefaultConfiguration->getServiceProvider()->willReturn($serviceProviderConfiguration);

        $defaultConfig = [
            'is_enabled'                    => false,
            'identity_provider_entity_id'   => '',
            'identity_provider_sign_on_url' => '',
            'identity_provider_logout_url'  => '',
            'identity_provider_certificate' => '',
            'service_provider_entity_id'    => 'https://sp.jambon.com/saml/metadata',
            'service_provider_certificate'  => 'default_certificate',
            'service_provider_private_key'  => 'default_private_key',
        ];

        $this->getAction()->shouldBeLike(new JsonResponse($defaultConfig));
    }
}
