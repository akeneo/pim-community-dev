<?php

namespace Specification\Akeneo\Platform\Component\Authentication\Sso\Configuration;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\Certificate;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Code;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\IsEnabled;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\EntityId;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\IdentityProvider;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Configuration;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\ServiceProvider;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Url;
use PhpSpec\ObjectBehavior;

class ConfigurationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith(
            new Code('jambon'),
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

        $this->shouldHaveType(Configuration::class);
    }

    function it_can_be_represented_as_an_array()
    {
        $this->beConstructedWith(
            new Code('jambon'),
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

        $this->toArray()->shouldReturn(
            [
                'isEnabled'        => true,
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
        $code = new Code('jambon');

        $this->beConstructedWith(
            $code,
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

        $this->code()->shouldReturn($code);
    }

    function it_can_be_built_from_an_array()
    {
        $this->beConstructedThrough(
            'fromArray',
            [
                'jambon',
                [
                    'isEnabled'        => true,
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
            ]
        );

        // We won't test the normalization again here but we have to make a call to trigger the instantiation.
        $this->toArray();
    }
}
