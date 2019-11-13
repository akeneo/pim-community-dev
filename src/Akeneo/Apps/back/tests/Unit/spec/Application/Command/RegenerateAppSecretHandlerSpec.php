<?php
declare(strict_types=1);

namespace spec\Akeneo\Apps\Application\Command;

use Akeneo\Apps\Application\Command\RegenerateAppSecretCommand;
use Akeneo\Apps\Application\Command\RegenerateAppSecretHandler;
use Akeneo\Apps\Application\Service\RegenerateClientSecret;
use Akeneo\Apps\Domain\Model\ValueObject\ClientId;
use Akeneo\Apps\Domain\Model\ValueObject\FlowType;
use Akeneo\Apps\Domain\Model\ValueObject\UserId;
use Akeneo\Apps\Domain\Model\Write\App;
use Akeneo\Apps\Domain\Persistence\Repository\AppRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RegenerateAppSecretHandlerSpec extends ObjectBehavior
{
    public function let(
        AppRepository $repository,
        RegenerateClientSecret $regenerateClientSecret
    ): void {
        $this->beConstructedWith($repository, $regenerateClientSecret);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(RegenerateAppSecretHandler::class);
    }

    public function it_regenerates_a_client_secret($repository, $regenerateClientSecret): void
    {
        $clientId = new ClientId(42);
        $userId = new UserId(72);
        $app = new App('magento', 'Magento Connector', FlowType::DATA_DESTINATION, $clientId, $userId);

        $repository->findOneByCode('magento')->willReturn($app);
        $regenerateClientSecret->execute($clientId)->shouldBeCalled();

        $command = new RegenerateAppSecretCommand('magento');
        $this->handle($command);
    }

    public function it_throws_an_exception_when_the_app_does_not_exist($repository, $regenerateClientSecret): void
    {
        $repository->findOneByCode('magento')->willReturn(null);
        $regenerateClientSecret->execute(Argument::any())->shouldNotBeCalled();

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('handle', [new RegenerateAppSecretCommand('magento')]);
    }
}
