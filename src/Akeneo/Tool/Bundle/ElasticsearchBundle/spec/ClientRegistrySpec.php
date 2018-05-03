<?php

namespace spec\Akeneo\Tool\Bundle\ElasticsearchBundle;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\ClientRegistry;
use PhpSpec\ObjectBehavior;

class ClientRegistrySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ClientRegistry::class);
    }

    function it_registers_an_es_client(Client $client)
    {
        $this->register($client);

        $this->getClients()->shouldReturn([$client]);
    }

    function it_registers_multiple_es_clients(Client $client1, Client $client2)
    {
        $this->register($client1);
        $this->register($client2);

        $this->getClients()->shouldReturn([$client1, $client2]);
    }

    function it_returns_an_empty_list_when_no_clients_has_been_registered()
    {
        $this->getClients()->shouldReturn([]);
    }
}
