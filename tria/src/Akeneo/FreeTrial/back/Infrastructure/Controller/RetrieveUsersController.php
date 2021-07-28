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

namespace Akeneo\FreeTrial\Infrastructure\Controller;

use Akeneo\FreeTrial\Domain\Model\InvitedUser;
use Akeneo\FreeTrial\Domain\Query\GetInvitedUsersQuery;
use Symfony\Component\HttpFoundation\JsonResponse;

class RetrieveUsersController
{
    private GetInvitedUsersQuery $getInvitedUsersQuery;

    public function __construct(GetInvitedUsersQuery $getInvitedUsersQuery)
    {
        $this->getInvitedUsersQuery = $getInvitedUsersQuery;
    }

    public function __invoke(): JsonResponse
    {
        $invitedUsers = $this->getInvitedUsersQuery->execute();

        $invitedUsers = array_map(fn (InvitedUser $invitedUser) => $invitedUser->toArray(), $invitedUsers);

        return new JsonResponse($invitedUsers);
    }
}
