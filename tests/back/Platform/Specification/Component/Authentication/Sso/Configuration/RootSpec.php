<?php

namespace Specification\Akeneo\Platform\Component\Authentication\Sso\Configuration;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\Certificate;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Code;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\EntityId;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\IdentityProvider;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Root;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\ServiceProvider;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Url;
use PhpSpec\ObjectBehavior;

class RootSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            Code::fromString('jambon'),
            new IdentityProvider(
                EntityId::fromString('https://idp.jambon.com'),
                Url::fromString('https://idp.jambon.com/'),
                Certificate::fromString('public_certificate')
            ),
            new ServiceProvider(
                EntityId::fromString('https://sp.jambon.com'),
                Certificate::fromString('public_certificate'),
                Certificate::fromString('private_certificate')
            )
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Root::class);
    }

    function it_can_be_represented_as_an_array()
    {
        $this->toArray()->shouldReturn(
            [
                'identityProvider' => [
                    'entityId'          => 'https://idp.jambon.com',
                    'url'               => 'https://idp.jambon.com/',
                    'publicCertificate' => 'public_certificate',
                ],
                'serviceProvider' => [
                    'entityId'           => 'https://sp.jambon.com',
                    'publicCertificate'  => 'public_certificate',
                    'privateCertificate' => 'private_certificate',
                ],
            ]
        );
    }

    function it_exposes_its_code()
    {
        $this->code()->shouldBeLike(Code::fromString('jambon'));
    }
}
