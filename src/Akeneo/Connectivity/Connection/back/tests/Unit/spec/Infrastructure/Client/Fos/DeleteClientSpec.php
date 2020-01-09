<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Client\Fos;

use Akeneo\Connectivity\Connection\Application\Settings\Service\DeleteClientInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ClientId;
use Akeneo\Connectivity\Connection\Infrastructure\Client\Fos\DeleteClient;
use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

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

    public function it_deletes_a_client($clientManager)
    {
        $client = new Client();
        $clientId = new ClientId(1);

        $clientManager->findClientBy(['id' => $clientId->id()])->willReturn($client);

        $clientManager->deleteClient($client)->shouldBeCalled();

        $this->execute($clientId);
    }

    public function it_throws_an_exception_if_client_not_found($clientManager)
    {
        $clientId = new ClientId(1);

        $clientManager->findClientBy(['id' => $clientId->id()])->willReturn(null);
        $clientManager->deleteClient(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(new \InvalidArgumentException('Client with id "1" not found.'))
            ->during('execute', [$clientId]);
    }
}
