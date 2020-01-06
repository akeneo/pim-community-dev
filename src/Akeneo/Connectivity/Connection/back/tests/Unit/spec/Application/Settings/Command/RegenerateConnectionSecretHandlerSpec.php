<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Settings\Command;

use Akeneo\Connectivity\Connection\Application\Settings\Command\RegenerateConnectionSecretCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\RegenerateConnectionSecretHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Service\RegenerateClientSecret;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ClientId;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\UserId;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RegenerateConnectionSecretHandlerSpec extends ObjectBehavior
{
    public function let(
        ConnectionRepository $repository,
        RegenerateClientSecret $regenerateClientSecret
    ): void {
        $this->beConstructedWith($repository, $regenerateClientSecret);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(RegenerateConnectionSecretHandler::class);
    }

    public function it_regenerates_a_client_secret($repository, $regenerateClientSecret): void
    {
        $userId = new UserId(72);
        $connection = new Connection('magento', 'Magento Connector', FlowType::DATA_DESTINATION, 42, $userId);

        $repository->findOneByCode('magento')->willReturn($connection);
        $regenerateClientSecret->execute(new ClientId(42))->shouldBeCalled();

        $command = new RegenerateConnectionSecretCommand('magento');
        $this->handle($command);
    }

    public function it_throws_an_exception_when_the_connection_does_not_exist($repository, $regenerateClientSecret): void
    {
        $repository->findOneByCode('magento')->willReturn(null);
        $regenerateClientSecret->execute(Argument::any())->shouldNotBeCalled();

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('handle', [new RegenerateConnectionSecretCommand('magento')]);
    }
}
