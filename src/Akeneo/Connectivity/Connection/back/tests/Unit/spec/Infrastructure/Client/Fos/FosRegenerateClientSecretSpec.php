<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Client\Fos;

use Akeneo\Connectivity\Connection\Application\Settings\Service\RegenerateClientSecret;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ClientId;
use Akeneo\Connectivity\Connection\Infrastructure\Client\Fos\FosRegenerateClientSecret;
use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Driver\Statement;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FosRegenerateClientSecretSpec extends ObjectBehavior
{
    public function let(ClientManagerInterface $clientManager, DbalConnection $dbalConnection): void
    {
        $this->beConstructedWith($clientManager, $dbalConnection);
    }

    public function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(FosRegenerateClientSecret::class);
        $this->shouldImplement(RegenerateClientSecret::class);
    }

    public function it_regenerates_a_client_secret(
        $clientManager,
        $dbalConnection,
        Client $client,
        Statement $stmt1,
        Statement $stmt2
    ) {
        $clientId = new ClientId(1);

        $clientManager->findClientBy(['id' => $clientId->id()])->willReturn($client);
        $client->setSecret(Argument::type('string'))->shouldBeCalled();
        $clientManager->updateClient($client)->shouldBeCalled();

        $dbalConnection->prepare(Argument::type('string'))->shouldBeCalledTimes(2);
        $dbalConnection->prepare('DELETE FROM pim_api_access_token WHERE client = :client_id')->willReturn($stmt1);
        $stmt1->execute(['client_id' => $clientId->id()])->shouldBeCalled();
        $dbalConnection->prepare('DELETE FROM pim_api_refresh_token WHERE client = :client_id')->willReturn($stmt2);
        $stmt2->execute(['client_id' => $clientId->id()])->shouldBeCalled();

        $this->execute($clientId);
    }

    public function it_throws_an_exception_if_client_not_found($clientManager, $dbalConnection)
    {
        $clientId = new ClientId(123);

        $clientManager->findClientBy(['id' => $clientId->id()])->willReturn(null);
        $clientManager->updateClient(Argument::any())->shouldNotBeCalled();

        $dbalConnection->prepare(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(new \InvalidArgumentException('Client with id "123" not found.'))
            ->during('execute', [$clientId]);
    }
}
