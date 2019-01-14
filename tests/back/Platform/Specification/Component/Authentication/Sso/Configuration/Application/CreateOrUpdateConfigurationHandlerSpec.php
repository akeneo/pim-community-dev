<?php

namespace Specification\Akeneo\Platform\Component\Authentication\Sso\Configuration\Application;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\Application\CreateOrUpdateConfiguration;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Configuration;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\Repository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CreateOrUpdateConfigurationHandlerSpec extends ObjectBehavior
{
    function let(Repository $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_handles_a_create_or_update_configuration_command($repository)
    {
        $config = new CreateOrUpdateConfiguration(
            'authentication_sso',
            true,
            'https://idp.jambon.com',
            'https://idp.jambon.com/signon',
            'https://idp.jambon.com/logout',
            'idp_certificate',
            'https://sp.jambon.com',
            'sp_certificate',
            'sp_private_key'
        );

        $repository->save(Argument::type(Configuration::class))->shouldBeCalled();

        $this->handle($config);
    }
}
