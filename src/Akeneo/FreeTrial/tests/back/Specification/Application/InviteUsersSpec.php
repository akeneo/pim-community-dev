<?php

declare(strict_types=1);

namespace Specification\Akeneo\FreeTrial\Application;

use Akeneo\FreeTrial\Domain\API\InviteUserAPI;
use Akeneo\FreeTrial\Domain\Model\InvitedUser;
use Akeneo\FreeTrial\Domain\Model\InviteUsersAcknowledge;
use Akeneo\FreeTrial\Domain\Repository\InvitedUserRepository;
use Akeneo\FreeTrial\Domain\ValueObject\InvitedUserStatus;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InviteUsersSpec extends ObjectBehavior
{
    public function let(InviteUserAPI $inviteUserAPI, InvitedUserRepository $invitedUserRepository)
    {
        $this->beConstructedWith($inviteUserAPI, $invitedUserRepository);
    }

    public function it_invites_users_with_success(InviteUserAPI $inviteUserAPI, InvitedUserRepository $invitedUserRepository)
    {
        $inviteUserAPI->inviteUser('toto@ziggy.com')->shouldBeCalled();
        $invitedUserRepository->save(new InvitedUser('toto@ziggy.com', InvitedUserStatus::invited()))->shouldBeCalled();
        $inviteUserAPI->inviteUser('titi@ziggy.com')->shouldBeCalled();
        $invitedUserRepository->save(new InvitedUser('titi@ziggy.com', InvitedUserStatus::invited()))->shouldBeCalled();

        $expectedAcknowledge = new InviteUsersAcknowledge();
        $expectedAcknowledge->success();

        $acknowledge = $this(['toto@ziggy.com', 'titi@ziggy.com']);
        $acknowledge->shouldBeLike($expectedAcknowledge);
    }

    public function it_invites_users_with_success_and_errors(InviteUserAPI $inviteUserAPI, InvitedUserRepository $invitedUserRepository)
    {

    }
}
