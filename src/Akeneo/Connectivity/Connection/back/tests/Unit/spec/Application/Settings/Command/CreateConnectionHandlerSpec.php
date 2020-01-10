<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Settings\Command;

use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionQuery;
use Akeneo\Connectivity\Connection\Application\Settings\Service\CreateClientInterface;
use Akeneo\Connectivity\Connection\Application\Settings\Service\CreateUserInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Exception\ConstraintViolationListException;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\Client;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\User;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateConnectionHandlerSpec extends ObjectBehavior
{
    public function let(
        ValidatorInterface $validator,
        ConnectionRepository $repository,
        CreateClientInterface $createClient,
        CreateUserInterface $createUser,
        FindAConnectionHandler $findAConnectionHandler
    ): void {
        $this->beConstructedWith($validator, $repository, $createClient, $createUser, $findAConnectionHandler);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(CreateConnectionHandler::class);
    }

    public function it_creates_a_connection(
        $validator,
        $repository,
        $createClient,
        $createUser,
        $findAConnectionHandler,
        ConnectionWithCredentials $connectionDTO
    ): void {
        $command = new CreateConnectionCommand('magento', 'Magento Connector', FlowType::DATA_DESTINATION);

        $violations = new ConstraintViolationList([]);
        $validator->validate($command)->willReturn($violations);

        $client = new Client(42, '42_myclientId', 'secret');
        $createClient->execute('Magento Connector')->shouldBeCalled()->willReturn($client);

        $user = new User(42, 'magento_app', 'my_client_pwd');
        $createUser->execute(Argument::type('string'), 'Magento Connector', ' ')->willReturn($user);

        $repository->create(Argument::type(Connection::class))->shouldBeCalled();

        $findAConnectionHandler
            ->handle(Argument::type(FindAConnectionQuery::class))
            ->shouldBeCalled()
            ->willReturn($connectionDTO);
        $connectionDTO->setPassword('my_client_pwd')->shouldBeCalled();

        $this->handle($command)->shouldReturn($connectionDTO);
    }

    public function it_returns_a_connection_with_credentials(
        $validator,
        $repository,
        $createClient,
        $createUser,
        $findAConnectionHandler
    ): void {
        $command = new CreateConnectionCommand('magento', 'Magento Connector', FlowType::DATA_DESTINATION);

        $violations = new ConstraintViolationList([]);
        $validator->validate($command)->willReturn($violations);

        $client = new Client(42, '42_myclientId', 'secret');
        $createClient->execute('Magento Connector')->shouldBeCalled()->willReturn($client);

        $user = new User(42, 'magento_app', 'my_client_pwd');
        $createUser->execute(Argument::type('string'), 'Magento Connector', ' ')->willReturn($user);

        $repository->create(Argument::type(Connection::class))->shouldBeCalled();

        $connection = new ConnectionWithCredentials(
            'magento',
            'Magento Connector',
            FlowType::DATA_DESTINATION,
            null,
            '42_myclientId',
            'secret',
            'magento_app',
            'user_role_id',
            'user_group_id'
        );
        $findAConnectionHandler
            ->handle(Argument::type(FindAConnectionQuery::class))
            ->shouldBeCalled()
            ->willReturn($connection);

        $connectionWithPassword = $this->handle($command);
        $connectionWithPassword->shouldBeAnInstanceOf(ConnectionWithCredentials::class);
        $connectionWithPassword->password()->shouldReturn('my_client_pwd');
    }

    public function it_throws_a_constraint_exception_when_something_is_invalid(
        $validator,
        $repository,
        $createClient,
        $createUser,
        ConstraintViolationInterface $violation
    ): void {
        $command = new CreateConnectionCommand('magento', 'Magento Connector', 'Wrong Flow Type');
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
