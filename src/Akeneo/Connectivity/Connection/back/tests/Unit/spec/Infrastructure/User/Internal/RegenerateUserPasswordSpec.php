<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\User\Internal;

use Akeneo\Connectivity\Connection\Application\Settings\Service\RegenerateUserPassword as RegenerateUserPasswordService;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\UserId;
use Akeneo\Connectivity\Connection\Infrastructure\User\Internal\RegenerateUserPassword;
use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Driver\Statement;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RegenerateUserPasswordSpec extends ObjectBehavior
{
    public function let(UserManager $userManager, DbalConnection $dbalConnection): void
    {
        $this->beConstructedWith($userManager, $dbalConnection);
    }

    public function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(RegenerateUserPassword::class);
        $this->shouldImplement(RegenerateUserPasswordService::class);
    }

    public function it_regenerates_a_user_password(
        $userManager,
        $dbalConnection,
        UserInterface $user,
        Statement $stmt1,
        Statement $stmt2
    ): void {
        $userId = new UserId(1);

        $userManager->findUserBy(['id' => $userId->id()])->willReturn($user);
        $user->setPlainPassword(Argument::type('string'))->shouldBeCalled();
        $userManager->updateUser($user)->shouldBeCalled();

        $dbalConnection->prepare(Argument::type('string'))->shouldBeCalledTimes(2);
        $dbalConnection->prepare('DELETE FROM pim_api_access_token WHERE user = :user_id')->willReturn($stmt1);
        $stmt1->execute(['user_id' => $userId->id()])->shouldBeCalled();
        $dbalConnection->prepare('DELETE FROM pim_api_refresh_token WHERE user = :user_id')->willReturn($stmt2);
        $stmt2->execute(['user_id' => $userId->id()])->shouldBeCalled();

        $this->execute($userId);
    }

    public function it_throws_an_exception_if_user_not_found($userManager, $dbalConnection)
    {
        $userId = new UserId(1);

        $userManager->findUserBy(['id' => $userId->id()])->willReturn(null);
        $userManager->updateUser(Argument::any())->shouldNotBeCalled();

        $dbalConnection->prepare(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(new \InvalidArgumentException('User with id "1" not found.'))
            ->during('execute', [$userId]);
    }
}
