<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\FreeTrial\Application;

use Akeneo\FreeTrial\Domain\API\InviteUserAPI;
use Akeneo\FreeTrial\Domain\Exception\InvitationException;
use Akeneo\FreeTrial\Domain\Model\InvitedUser;
use Akeneo\FreeTrial\Domain\Model\InviteUsersAcknowledge;
use Akeneo\FreeTrial\Domain\Repository\InvitedUserRepository;
use Akeneo\FreeTrial\Domain\ValueObject\InvitedUserStatus;

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
        $emails = array_unique($emails);
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
