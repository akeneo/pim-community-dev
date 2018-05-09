<?php

namespace spec\Pim\Bundle\ApiBundle\Command;

use OAuth2\OAuth2;
use Prophecy\Argument;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\ApiBundle\Entity\Client;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CreateClientCommandSpec extends ObjectBehavior
{
    function let(InputInterface $input, Client $client, EncoderInterface $encoder)
    {
        $input->bind(Argument::cetera())->willReturn();
        $input->getArgument(Argument::cetera())->willReturn(Argument::cetera());
        $input->hasArgument(Argument::cetera())->willReturn(true);
        $input->isInteractive()->willReturn(false);

        $input->getOption('format')->willReturn('xml');
        $input->getOption('redirect_uri')->willReturn([]);
        $input->getOption('grant_type')->willReturn([]);
        $input->hasOption('label')->willReturn(false);
        $input->validate()->willReturn();

        $client->getLabel()->willReturn(null);
        $client->setRedirectUris([])->willReturn();
        $client->setAllowedGrantTypes(Argument::cetera())->willReturn();
        $client->getPublicId()->willReturn();
        $client->getSecret()->willReturn();

        $encoder->encode(Argument::cetera())->willReturn();
    }

    function it_is_a_container_aware_command()
    {
        $this->shouldHaveType('Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim:oauth-server:create-client');
    }

    function it_runs_the_fos_client_manager_and_serializer(
        ClientManagerInterface $clientManager,
        ContainerInterface $container,
        EncoderInterface $encoder,
        InputInterface $input,
        OutputInterface $output,
        Client $client
    )
    {
        $container->get('fos_oauth_server.client_manager.default')->willReturn($clientManager);
        $container->get('pim_serializer')->willReturn($encoder);

        $clientManager->createClient()->shouldBeCalled()->willReturn($client);
        $clientManager->updateClient($client)->shouldBeCalled();

        $encoder->encode(Argument::cetera())->shouldBeCalled();

        $this->setContainer($container);
        $this->run($input, $output);
    }

    function it_runs_the_fos_client_manager_only(
        ClientManagerInterface $clientManager,
        ContainerInterface $container,
        EncoderInterface $encoder,
        InputInterface $input,
        OutputInterface $output,
        Client $client
    )
    {
        $input->getOption('format')->willReturn('txt');
        $container->get('fos_oauth_server.client_manager.default')->willReturn($clientManager);

        // prophecy can't mock Serializer::encode as it is final method
        $container->get('pim_serializer')->willReturn($encoder);

        $clientManager->createClient()->shouldBeCalled()->willReturn($client);
        $clientManager->updateClient($client)->shouldBeCalled();

        $encoder->encode(Argument::cetera())->shouldNotBeCalled();

        $this->setContainer($container);
        $this->run($input, $output);
    }
}
