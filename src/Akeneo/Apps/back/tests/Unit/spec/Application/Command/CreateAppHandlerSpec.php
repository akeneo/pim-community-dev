<?php
declare(strict_types=1);

namespace spec\Akeneo\Apps\Application\Command;

use Akeneo\Apps\Application\Command\CreateAppCommand;
use Akeneo\Apps\Application\Command\CreateAppHandler;
use Akeneo\Apps\Application\Service\CreateClientInterface;
use Akeneo\Apps\Domain\Exception\ConstraintViolationListException;
use Akeneo\Apps\Domain\Model\ValueObject\ClientId;
use Akeneo\Apps\Domain\Model\Write\App;
use Akeneo\Apps\Domain\Model\ValueObject\FlowType;
use Akeneo\Apps\Domain\Persistence\Repository\AppRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateAppHandlerSpec extends ObjectBehavior
{
    public function let(
        ValidatorInterface $validator,
        AppRepository $repository,
        CreateClientInterface $createClient
    ): void {
        $this->beConstructedWith($validator, $repository, $createClient);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(CreateAppHandler::class);
    }

    public function it_creates_an_app(
        $validator,
        $repository,
        $createClient,
        ConstraintViolationListInterface $constraintViolationList
    ): void {
        $command = new CreateAppCommand('magento', 'Magento Connector', FlowType::DATA_DESTINATION);

        $validator->validate($command)->willReturn($constraintViolationList);
        $constraintViolationList->count()->willReturn(0);

        $clientId = ClientId::create(42);
        $createClient->execute('Magento Connector')->shouldBeCalled()->willReturn($clientId);

        $repository->create(Argument::type(App::class))->shouldBeCalled();

        $this->handle($command);
    }

    public function it_throws_a_constraint_exception_when_something_is_invalid(
        $validator,
        $repository,
        $createClient,
        ConstraintViolationListInterface $constraintViolationList
    ): void {
        $command = new CreateAppCommand('magento', 'Magento Connector', 'Wrong Flow Type');

        $validator->validate($command)->willReturn($constraintViolationList);
        $constraintViolationList->count()->willReturn(1);

        $createClient->execute($command->label())->shouldNotBeCalled();
        $repository->create(Argument::any())->shouldNotBeCalled();

        $this
            ->shouldThrow(ConstraintViolationListException::class)
            ->during('handle', [$command]);
    }
}
