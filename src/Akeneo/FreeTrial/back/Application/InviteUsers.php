<?php

declare(strict_types=1);

namespace Akeneo\FreeTrial\Application;

use Akeneo\FreeTrial\Domain\API\InviteUserAPI;
use Akeneo\FreeTrial\Domain\Exception\InvitationException;
use Akeneo\FreeTrial\Domain\Model\InvitedUser;
use Akeneo\FreeTrial\Domain\Model\InviteUsersAcknowledge;
use Akeneo\FreeTrial\Domain\Repository\InvitedUserRepository;
use Akeneo\FreeTrial\Domain\ValueObject\InvitedUserStatus;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InviteUsers
{
    private InviteUserAPI $inviteUserAPI;

    private InvitedUserRepository $invitedUserRepository;

    public function __construct(InviteUserAPI $inviteUserAPI, InvitedUserRepository $invitedUserRepository)
    {
        $this->inviteUserAPI = $inviteUserAPI;
        $this->invitedUserRepository = $invitedUserRepository;
    }

    public function __invoke(array $emails): InviteUsersAcknowledge
    {
        $acknowledge = new InviteUsersAcknowledge();
        foreach ($emails as $email) {
            try {
                $user = new InvitedUser($email, InvitedUserStatus::invited());

                $this->inviteUserAPI->inviteUser($email);
                $this->invitedUserRepository->save($user);
                $acknowledge->success();
            } catch (InvitationException $e) {
                $acknowledge->error($e->getErrorCode());
            }
        }

        return $acknowledge;
    }
}
