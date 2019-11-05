<?php
declare(strict_types=1);

namespace spec\Akeneo\Apps\Application\Command;

use Akeneo\Apps\Application\Command\CreateAppCommand;
use Akeneo\Apps\Application\Command\CreateAppHandler;
use Akeneo\Apps\Application\Service\CreateClientInterface;
use Akeneo\Apps\Application\Service\CreateUserInterface;
use Akeneo\Apps\Domain\Exception\ConstraintViolationListException;
use Akeneo\Apps\Domain\Model\ValueObject\ClientId;
use Akeneo\Apps\Domain\Model\ValueObject\FlowType;
use Akeneo\Apps\Domain\Model\Write\App;
use Akeneo\Apps\Domain\Persistence\Repository\AppRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateAppHandlerSpec extends ObjectBehavior
{
    public function let(
        ValidatorInterface $validator,
        AppRepository $repository,
        CreateClientInterface $createClient,
        CreateUserInterface $createUser
    ): void {
        $this->beConstructedWith($validator, $repository, $createClient, $createUser);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(CreateAppHandler::class);
    }

    public function it_creates_an_app(
        $validator,
        $repository,
        $createClient,
        $createUser
    ): void {
        $command = new CreateAppCommand('magento', 'Magento Connector', FlowType::DATA_DESTINATION);

        $violations = new ConstraintViolationList([]);
        $validator->validate($command)->willReturn($violations);

        $clientId = new ClientId(42);
        $createClient->execute('Magento Connector')->shouldBeCalled()->willReturn($clientId);
        $createUser->execute('magento', 'Magento Connector', 'APP', 'magento', Argument::any())->shouldBeCalled();

        $repository->create(Argument::type(App::class))->shouldBeCalled();

        $this->handle($command);
    }

    public function it_throws_a_constraint_exception_when_something_is_invalid(
        $validator,
        $repository,
        $createClient,
        $createUser,
        ConstraintViolationInterface $violation
    ): void {
        $command = new CreateAppCommand('magento', 'Magento Connector', 'Wrong Flow Type');
        $violations = new ConstraintViolationList([$violation->getWrappedObject()]);
        $validator->validate($command)->willReturn($violations);

        $createClient->execute(Argument::any())->shouldNotBeCalled();
        $createUser->execute(Argument::cetera())->shouldNotBeCalled();
        $repository->create(Argument::any())->shouldNotBeCalled();

        $this
            ->shouldThrow(ConstraintViolationListException::class)
            ->during('handle', [$command]);
    }
}
