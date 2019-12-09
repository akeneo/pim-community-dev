<?php
declare(strict_types=1);

namespace spec\Akeneo\Apps\Application\Command;

use Akeneo\Apps\Application\Command\CreateAppCommand;
use Akeneo\Apps\Application\Command\CreateAppHandler;
use Akeneo\Apps\Application\Service\CreateClientInterface;
use Akeneo\Apps\Application\Service\CreateUserInterface;
use Akeneo\Apps\Domain\Exception\ConstraintViolationListException;
use Akeneo\Apps\Domain\Model\Read\AppWithCredentials;
use Akeneo\Apps\Domain\Model\Read\Client;
use Akeneo\Apps\Domain\Model\Read\User;
use Akeneo\Apps\Domain\Model\ValueObject\ClientId;
use Akeneo\Apps\Domain\Model\ValueObject\FlowType;
use Akeneo\Apps\Domain\Model\Write\App;
use Akeneo\Apps\Domain\Persistence\Repository\AppRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
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

        $client = new Client(42, '42_myclientId', 'secret');
        $createClient->execute('Magento Connector')->shouldBeCalled()->willReturn($client);

        $user = new User(42, 'magento_app', 'my_client_pwd');
        $createUser->execute(Argument::type('string'), 'Magento Connector', ' ')->willReturn($user);

        $repository->create(Argument::type(App::class))->shouldBeCalled();

        $this->handle($command);
    }

    public function it_returns_an_app_with_credentials(
        $validator,
        $repository,
        $createClient,
        $createUser
    ): void {
        $command = new CreateAppCommand('magento', 'Magento Connector', FlowType::DATA_DESTINATION);

        $violations = new ConstraintViolationList([]);
        $validator->validate($command)->willReturn($violations);

        $client = new Client(42, '42_myclientId', 'secret');
        $createClient->execute('Magento Connector')->shouldBeCalled()->willReturn($client);

        $user = new User(42, 'magento_app', 'my_client_pwd');
        $createUser->execute(Argument::type('string'), 'Magento Connector', ' ')->willReturn($user);

        $repository->create(Argument::type(App::class))->shouldBeCalled();

        $appWithCredentials = $this->handle($command);
        $appWithCredentials->shouldBeAnInstanceOf(AppWithCredentials::class);
        $appWithCredentials->code()->shouldReturn('magento');
        $appWithCredentials->label()->shouldReturn('Magento Connector');
        $appWithCredentials->flowType()->shouldReturn(FlowType::DATA_DESTINATION);
        $appWithCredentials->clientId()->shouldReturn('42_myclientId');
        $appWithCredentials->secret()->shouldReturn('secret');
        $appWithCredentials->username()->shouldReturn('magento_app');
        $appWithCredentials->password()->shouldReturn('my_client_pwd');
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
