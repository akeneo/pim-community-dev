<?php
declare(strict_types=1);

namespace spec\Akeneo\Apps\Application\Command;

use Akeneo\Apps\Application\Command\CreateAppCommand;
use Akeneo\Apps\Application\Command\CreateAppHandler;
use Akeneo\Apps\Application\Service\CreateClientInterface;
use Akeneo\Apps\Domain\Model\Write\App;
use Akeneo\Apps\Domain\Model\Write\AppCode;
use Akeneo\Apps\Domain\Model\Write\AppLabel;
use Akeneo\Apps\Domain\Model\ClientId;
use Akeneo\Apps\Domain\Model\Write\FlowType;
use Akeneo\Apps\Domain\Persistence\Repository\AppRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CreateAppHandlerSpec extends ObjectBehavior
{
    public function let(AppRepository $repository, CreateClientInterface $createClient): void
    {
        $this->beConstructedWith($repository, $createClient);
    }

    public function it_is_a_create_app_handler(): void
    {
        $this->shouldHaveType(CreateAppHandler::class);
    }

    public function it_handles_an_app_creation($repository, $createClient): void
    {
        $command = new CreateAppCommand(
            AppCode::create('code'),
            AppLabel::create('label'),
            FlowType::create(FlowType::DATA_DESTINATION)
        );
        $clientId = ClientId::create(42);
        $createClient->execute('label')->shouldBeCalled()->willReturn($clientId);

        $repository->create(Argument::type(App::class))->shouldBeCalled();

        $this->handle($command);
    }
}
