<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Settings\Command;

use Akeneo\Connectivity\Connection\Application\Settings\Command\RegenerateConnectionPasswordCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\RegenerateConnectionPasswordHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Service\RegenerateUserPassword;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\UserId;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Pierre Jolly <pierre/jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RegenerateConnectionPasswordHandlerSpec extends ObjectBehavior
{
    public function let(
        ConnectionRepository $repository,
        RegenerateUserPassword $regenerateUserPassword
    ): void {
        $this->beConstructedWith($repository, $regenerateUserPassword);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(RegenerateConnectionPasswordHandler::class);
    }

    public function it_regenerates_a_user_password($repository, $regenerateUserPassword): void
    {
        $userId = new UserId(72);
        $connection = new Connection('magento', 'Magento Connector', FlowType::DATA_DESTINATION, 42, $userId);

        $repository->findOneByCode('magento')->willReturn($connection);
        $regenerateUserPassword->execute($userId)->shouldBeCalled();

        $command = new RegenerateConnectionPasswordCommand('magento');
        $this->handle($command);
    }

    public function it_throws_an_exception_when_the_connection_does_not_exist($repository, $regenerateUserPassword): void
    {
        $repository->findOneByCode('magento')->willReturn(null);
        $regenerateUserPassword->execute(Argument::any())->shouldNotBeCalled();

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('handle', [new RegenerateConnectionPasswordCommand('magento')]);
    }
}
