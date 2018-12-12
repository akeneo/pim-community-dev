<?php

namespace Specification\Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\UI;

use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\UI\Normalizer;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Certificate;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Code;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Configuration;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\IsEnabled;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\EntityId;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\IdentityProvider;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\ServiceProvider;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Url;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class NormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Normalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_configuration_objects_only()
    {
        $config = $this->getConfigInstance();
        $this->supportsNormalization($config, 'internal_api')->shouldReturn(true);

        $this->supportsNormalization(new \stdClass())->shouldReturn(false);
    }

    function it_supports_internal_api_format_only()
    {
        $config = $this->getConfigInstance();
        $this->supportsNormalization($config, 'internal_api')->shouldReturn(true);
        $this->supportsNormalization($config, 'standard')->shouldReturn(false);
    }

    function it_normalizes_a_configuration_object()
    {
        $config = $this->getConfigInstance();
        $this->normalize($config)->shouldReturn(
            [
                'is_enabled'                              => true,
                'identity_provider_entity_id'          => 'https://idp.jambon.com',
                'identity_provider_url'                => 'https://idp.jambon.com/',
                'identity_provider_public_certificate' => 'public_certificate',
                'service_provider_entity_id'           => 'https://sp.jambon.com',
                'service_provider_public_certificate'  => 'public_certificate',
                'service_provider_private_certificate' => 'private_certificate',
            ]
        );
    }

    private function getConfigInstance(): Configuration
    {
        return new Configuration(
            new Code('authentication_sso'),
            new IsEnabled(true),
            new IdentityProvider(
                new EntityId('https://idp.jambon.com'),
                new Url('https://idp.jambon.com/'),
                new Certificate('public_certificate')
            ),
            new ServiceProvider(
                new EntityId('https://sp.jambon.com'),
                new Certificate('public_certificate'),
                new Certificate('private_certificate')
            )
        );
    }
}
