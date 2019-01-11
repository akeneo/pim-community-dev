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
            new EntityId('https://sp.jambon.com'),
            new Certificate('certificate'),
            new Certificate('private_key')
        );

        $this->shouldHaveType(ServiceProvider::class);
    }

    function it_can_be_built_from_an_array()
    {
        $this->beConstructedThrough('fromArray', [
            [
                'entityId'    => 'https://sp.jambon.com',
                'certificate' => 'certificate',
                'privateKey'  => 'private_key',
            ]
        ]);

        $this->toArray()->shouldReturn(
            [
                'entityId'    => 'https://sp.jambon.com',
                'certificate' => 'certificate',
                'privateKey'  => 'private_key',
            ]
        );
    }
}
