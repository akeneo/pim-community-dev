<?php
declare(strict_types=1);

namespace spec\Akeneo\Apps\Application\Settings\Command;

use Akeneo\Apps\Application\Settings\Command\UpdateConnectionCommand;
use Akeneo\Apps\Application\Settings\Command\UpdateConnectionHandler;
use Akeneo\Apps\Domain\Settings\Exception\ConstraintViolationListException;
use Akeneo\Apps\Domain\Settings\Model\ValueObject\ClientId;
use Akeneo\Apps\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Apps\Domain\Settings\Model\ValueObject\UserId;
use Akeneo\Apps\Domain\Settings\Model\Write\Connection;
use Akeneo\Apps\Domain\Settings\Persistence\Repository\ConnectionRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateConnectionHandlerSpec extends ObjectBehavior
{
    public function let(
        ValidatorInterface $validator,
        ConnectionRepository $repository
    ): void {
        $this->beConstructedWith($validator, $repository);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(UpdateConnectionHandler::class);
    }

    public function it_updates_a_connection(
        $validator,
        $repository
    ): void {
        $command = new UpdateConnectionCommand('magento', 'Pimgento', FlowType::DATA_DESTINATION);

        $violations = new ConstraintViolationList([]);
        $validator->validate($command)->willReturn($violations);

        $connection = new Connection('magento', 'Magento Connector', FlowType::OTHER, 42, new UserId(50));
        $repository->findOneByCode('magento')->willReturn($connection);
        $repository->update(Argument::type(Connection::class))->shouldBeCalled();

        $this->handle($command);
    }

    public function it_throws_a_constraint_exception_when_something_is_invalid(
        $validator,
        $repository,
        ConstraintViolationInterface $violation
    ): void {
        $command = new UpdateConnectionCommand('magento', 'Pimgento', 'Wrong flow type');

        $violations = new ConstraintViolationList([$violation->getWrappedObject()]);
        $validator->validate($command)->willReturn($violations);

        $repository->findOneByCode('magento')->shouldNotBeCalled();
        $repository->update(Argument::type(Connection::class))->shouldNotBeCalled();

        $this
            ->shouldThrow(ConstraintViolationListException::class)
            ->during('handle', [$command]);
    }
}
