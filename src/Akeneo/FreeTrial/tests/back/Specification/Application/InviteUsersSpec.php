<?php

declare(strict_types=1);

namespace Specification\Akeneo\FreeTrial\Application;

use Akeneo\FreeTrial\Domain\API\InviteUserAPI;
use Akeneo\FreeTrial\Domain\Exception\InvalidEmailException;
use Akeneo\FreeTrial\Domain\Exception\InvitationAlreadySentException;
use Akeneo\FreeTrial\Domain\Exception\InvitationFailedException;
use Akeneo\FreeTrial\Domain\Model\InvitedUser;
use Akeneo\FreeTrial\Domain\Model\InviteUsersAcknowledge;
use Akeneo\FreeTrial\Domain\Repository\InvitedUserRepository;
use Akeneo\FreeTrial\Domain\ValueObject\InvitedUserStatus;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

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

        $invitedUserRepository->save(new InvitedUser('toto@ziggy.com', InvitedUserStatus::invited()))->shouldBeCalled();
        $invitedUserRepository->save(new InvitedUser('titi@ziggy.com', InvitedUserStatus::invited()))->shouldBeCalled();

        $expectedAcknowledge = new InviteUsersAcknowledge();
        $expectedAcknowledge->success();

        $acknowledge = $this(['toto@ziggy.com', 'titi@ziggy.com']);
        $acknowledge->shouldBeLike($expectedAcknowledge);
    }

    public function it_invites_users_with_only_errors(InviteUserAPI $inviteUserAPI, InvitedUserRepository $invitedUserRepository)
    {
        $inviteUserAPI->inviteUser('toto@ziggy.com')->shouldBeCalled()->willThrow(new InvalidEmailException());
        $inviteUserAPI->inviteUser('titi@ziggy.com')->shouldBeCalled()->willThrow(new InvitationAlreadySentException());
        $inviteUserAPI->inviteUser('tata@ziggy.com')->shouldBeCalled()->willThrow(new InvitationFailedException());

        $invitedUserRepository->save(Argument::any())->shouldNotBeCalled();

        $expectedAcknowledge = new InviteUsersAcknowledge();
        $expectedAcknowledge->error(InvalidEmailException::ERROR_CODE);
        $expectedAcknowledge->error(InvitationAlreadySentException::ERROR_CODE);
        $expectedAcknowledge->error(InvitationFailedException::ERROR_CODE);

        $acknowledge = $this(['toto@ziggy.com', 'titi@ziggy.com', 'tata@ziggy.com']);
        $acknowledge->shouldBeLike($expectedAcknowledge);
    }

    public function it_invites_users_with_successes_and_errors(InviteUserAPI $inviteUserAPI, InvitedUserRepository $invitedUserRepository)
    {
        $inviteUserAPI->inviteUser('toto@ziggy.com')->shouldBeCalled()->willThrow(new InvitationAlreadySentException());
        $inviteUserAPI->inviteUser('titi@ziggy.com')->shouldBeCalled();
        $inviteUserAPI->inviteUser('tata@ziggy.com')->shouldBeCalled()->willThrow(new InvitationFailedException());
        $inviteUserAPI->inviteUser('')->shouldNotBeCalled();

        $invitedUserRepository->save(new InvitedUser('titi@ziggy.com', InvitedUserStatus::invited()))->shouldBeCalled();

        $expectedAcknowledge = new InviteUsersAcknowledge();
        $expectedAcknowledge->error(InvitationAlreadySentException::ERROR_CODE);
        $expectedAcknowledge->error(InvitationFailedException::ERROR_CODE);
        $expectedAcknowledge->error(InvalidEmailException::ERROR_CODE);
        $expectedAcknowledge->success();

        $acknowledge = $this(['toto@ziggy.com', 'titi@ziggy.com', 'tata@ziggy.com', '']);
        $acknowledge->shouldBeLike($expectedAcknowledge);
    }
}
