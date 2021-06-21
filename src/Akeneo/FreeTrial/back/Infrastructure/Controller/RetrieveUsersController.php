<?php

declare(strict_types=1);

namespace Akeneo\FreeTrial\Infrastructure\Controller;

use Akeneo\FreeTrial\Domain\Model\InvitedUser;
use Akeneo\FreeTrial\Domain\Query\GetInvitedUsersQuery;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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