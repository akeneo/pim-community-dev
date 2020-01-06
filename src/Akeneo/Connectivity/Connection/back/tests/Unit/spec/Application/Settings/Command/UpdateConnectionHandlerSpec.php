<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Settings\Command;

use Akeneo\Connectivity\Connection\Application\Settings\Command\UpdateConnectionCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\UpdateConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Service\UpdateUserPermissionsInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Exception\ConstraintViolationListException;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\UserId;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateConnectionHandlerSpec extends ObjectBehavior
{
    public function let(
        ValidatorInterface $validator,
        ConnectionRepository $repository,
        UpdateUserPermissionsInterface $updateUserPermissions
    ): void {
        $this->beConstructedWith($validator, $repository, $updateUserPermissions);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(UpdateConnectionHandler::class);
    }

    public function it_updates_a_connection(
        $validator,
        $repository
    ): void {
        $command = new UpdateConnectionCommand(
            'magento',
            'Pimgento',
            FlowType::DATA_DESTINATION,
            null,
            '1',
            '2'
        );

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
        $command = new UpdateConnectionCommand(
            'magento',
            'Pimgento',
            'Wrong flow type',
            null,
            '1',
            '2'
        );

        $violations = new ConstraintViolationList([$violation->getWrappedObject()]);
        $validator->validate($command)->willReturn($violations);

        $repository->findOneByCode('magento')->shouldNotBeCalled();
        $repository->update(Argument::type(Connection::class))->shouldNotBeCalled();

        $this
            ->shouldThrow(ConstraintViolationListException::class)
            ->during('handle', [$command]);
    }
}
