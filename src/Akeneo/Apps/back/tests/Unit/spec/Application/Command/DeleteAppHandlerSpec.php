<?php

declare(strict_types=1);

namespace spec\Akeneo\Apps\Application\Command;

use Akeneo\Apps\Application\Command\DeleteAppCommand;
use Akeneo\Apps\Application\Command\DeleteAppHandler;
use Akeneo\Apps\Application\Service\DeleteClientInterface;
use Akeneo\Apps\Application\Service\DeleteUserInterface;
use Akeneo\Apps\Domain\Model\ValueObject\ClientId;
use Akeneo\Apps\Domain\Model\ValueObject\FlowType;
use Akeneo\Apps\Domain\Model\ValueObject\UserId;
use Akeneo\Apps\Domain\Model\Write\App;
use Akeneo\Apps\Domain\Persistence\Repository\AppRepository;
use PhpSpec\ObjectBehavior;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DeleteAppHandlerSpec extends ObjectBehavior
{
    public function let(
        AppRepository $repository,
        DeleteClientInterface $deleteClient,
        DeleteUserInterface $deleteUser
    ): void {
        $this->beConstructedWith($repository, $deleteClient, $deleteUser);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(DeleteAppHandler::class);
    }

    public function it_deletes_an_app(
        $repository,
        $deleteClient,
        $deleteUser
    ): void {
        $magentoClientId = new ClientId(1);
        $magentoUserId = new UserId(1);
        $magentoApp = new App('magento', 'Magento', FlowType::OTHER, 1, $magentoUserId);

        $command = new DeleteAppCommand((string) $magentoApp->code());

        $repository->findOneByCode('magento')->willReturn($magentoApp);
        $repository->delete($magentoApp)->shouldBeCalled();

        $deleteClient->execute($magentoClientId)->shouldBeCalled();
        $deleteUser->execute($magentoUserId)->shouldBeCalled();

        $this->handle($command);
    }
}
