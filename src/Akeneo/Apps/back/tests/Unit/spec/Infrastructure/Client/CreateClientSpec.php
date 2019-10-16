<?php
declare(strict_types=1);

namespace spec\Akeneo\Apps\Infrastructure\Client;

use Akeneo\Apps\Application\Service\CreateClientInterface;
use Akeneo\Apps\Domain\Model\ClientId;
use Akeneo\Apps\Infrastructure\Client\CreateClient;
use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CreateClientSpec extends ObjectBehavior
{
    public function let(ClientManagerInterface $clientManager): void
    {
        $this->beConstructedWith($clientManager);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(CreateClient::class);
        $this->shouldImplement(CreateClientInterface::class);
    }

    public function it_creates_a_client_with_a_label(Client $client, $clientManager)
    {
        $clientManager->createClient()->willReturn($client);
        $client->setLabel('new_app')->shouldBeCalled();
        $client
            ->setAllowedGrantTypes([OAuth2::GRANT_TYPE_USER_CREDENTIALS, OAuth2::GRANT_TYPE_REFRESH_TOKEN])
            ->shouldBeCalled();
        $clientManager->updateClient($client)->shouldBeCalled();
        $client->getId()->willReturn(1);

        $clientId = $this->execute('new_app');
        $clientId->shouldBeAnInstanceOf(ClientId::class);
        $clientId->id()->shouldReturn(1);
    }
}
