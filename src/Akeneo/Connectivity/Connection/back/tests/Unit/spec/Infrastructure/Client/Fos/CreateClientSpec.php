<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Client\Fos;

use Akeneo\Connectivity\Connection\Application\Settings\Service\CreateClientInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\Client;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ClientId;
use Akeneo\Connectivity\Connection\Infrastructure\Client\Fos\CreateClient;
use Akeneo\Tool\Bundle\ApiBundle\Entity\Client as FosClient;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2;
use PhpSpec\ObjectBehavior;

class CreateClientSpec extends ObjectBehavior
{
    public function let(ClientManagerInterface $clientManager): void
    {
        $this->beConstructedWith($clientManager);
    }

    public function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(CreateClient::class);
        $this->shouldImplement(CreateClientInterface::class);
    }

    public function it_creates_a_client_with_a_label(FosClient $fosClient, $clientManager)
    {
        $clientManager->createClient()->willReturn($fosClient);
        $fosClient->setLabel('new_app')->shouldBeCalled();
        $fosClient
            ->setAllowedGrantTypes([OAuth2::GRANT_TYPE_USER_CREDENTIALS, OAuth2::GRANT_TYPE_REFRESH_TOKEN])
            ->shouldBeCalled();

        $clientManager->updateClient($fosClient)->shouldBeCalled();

        $fosClient->getId()->willReturn(1);
        $fosClient->getPublicId()->willReturn('1_myclientid');
        $fosClient->getSecret()->willReturn('my_client_secret');

        $clientVO = $this->execute('new_app');
        $clientVO->shouldBeAnInstanceOf(Client::class);
        $clientVO->id()->shouldReturn(1);
        $clientVO->clientId()->shouldReturn('1_myclientid');
        $clientVO->secret()->shouldReturn('my_client_secret');
    }
}
