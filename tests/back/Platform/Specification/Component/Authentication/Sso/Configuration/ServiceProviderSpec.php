<?php

namespace Specification\Akeneo\Platform\Component\Authentication\Sso\Configuration;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\Certificate;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\EntityId;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\ServiceProvider;
use PhpSpec\ObjectBehavior;

class ServiceProviderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith(
            EntityId::fromString('https://sp.jambon.com'),
            Certificate::fromString('public_certificate'),
            Certificate::fromString('private_certificate')
        );

        $this->shouldHaveType(ServiceProvider::class);
    }

    function it_can_be_built_from_an_array()
    {
        $this->beConstructedThrough('fromArray', [[
            'entityId'           => 'https://sp.jambon.com',
            'publicCertificate'  => 'public_certificate',
            'privateCertificate' => 'private_certificate',
        ]]);

        $this->toArray()->shouldReturn(
            [
                'entityId'           => 'https://sp.jambon.com',
                'publicCertificate'  => 'public_certificate',
                'privateCertificate' => 'private_certificate',
            ]
        );
    }
}
