<?php
declare(strict_types=1);

namespace spec\Akeneo\Apps\Infrastructure\Client\Fos;

use Akeneo\Apps\Application\Service\DeleteClientInterface;
use Akeneo\Apps\Domain\Model\ValueObject\ClientId;
use Akeneo\Apps\Infrastructure\Client\Fos\DeleteClient;
use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2;
use PhpSpec\ObjectBehavior;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DeleteClientSpec extends ObjectBehavior
{
    public function let(ClientManagerInterface $clientManager): void
    {
        $this->beConstructedWith($clientManager);
    }

    public function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(DeleteClient::class);
        $this->shouldImplement(DeleteClientInterface::class);
    }

    public function it_deletes_a_client(Client $client, $clientManager)
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
