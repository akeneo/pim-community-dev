<?php

namespace Specification\Akeneo\Platform\Component\Authentication\Sso\Configuration;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\Certificate;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\EntityId;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\IdentityProvider;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Url;
use PhpSpec\ObjectBehavior;

class IdentityProviderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith(
            new EntityId('https://idp.jambon.com'),
            new Url('https://idp.jambon.com/'),
            new Certificate('public_certificate')
        );

        $this->shouldHaveType(IdentityProvider::class);
    }

    function it_can_be_built_from_an_array()
    {
        $this->beConstructedThrough('fromArray', [[
            'entityId'          => 'https://idp.jambon.com',
            'url'               => 'https://idp.jambon.com/',
            'publicCertificate' => 'public_certificate',
        ]]);

        $this->toArray()->shouldReturn(
            [
                'entityId'          => 'https://idp.jambon.com',
                'url'               => 'https://idp.jambon.com/',
                'publicCertificate' => 'public_certificate',
            ]
        );
    }
}
