<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Settings\Command;

use Akeneo\Connectivity\Connection\Application\Settings\Command\DeleteConnectionCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\DeleteConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Service\DeleteClientInterface;
use Akeneo\Connectivity\Connection\Application\Settings\Service\DeleteUserInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ClientId;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\UserId;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepository;
use PhpSpec\ObjectBehavior;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DeleteConnectionHandlerSpec extends ObjectBehavior
{
    public function let(
        ConnectionRepository $repository,
        DeleteClientInterface $deleteClient,
        DeleteUserInterface $deleteUser
    ): void {
        $this->beConstructedWith($repository, $deleteClient, $deleteUser);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(DeleteConnectionHandler::class);
    }

    public function it_deletes_a_connection(
        $repository,
        $deleteClient,
        $deleteUser
    ): void {
        $magentoClientId = new ClientId(1);
        $magentoUserId = new UserId(1);
        $magentoConnection = new Connection('magento', 'Magento', FlowType::OTHER, 1, $magentoUserId);

        $command = new DeleteConnectionCommand((string) $magentoConnection->code());

        $repository->findOneByCode('magento')->willReturn($magentoConnection);
        $repository->delete($magentoConnection)->shouldBeCalled();

        $deleteClient->execute($magentoClientId)->shouldBeCalled();
        $deleteUser->execute($magentoUserId)->shouldBeCalled();

        $this->handle($command);
    }
}
