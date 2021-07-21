<?php

declare(strict_types=1);

namespace Akeneo\FreeTrial\Infrastructure\Controller;

use Akeneo\FreeTrial\Application\InviteUsers;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SaveUsersController
{
    private InviteUsers $inviteUser;

    public function __construct(InviteUsers $inviteUser)
    {
        $this->inviteUser = $inviteUser;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $emails = json_decode($request->getContent());

        $acknowledge = ($this->inviteUser)($emails);

        return new JsonResponse($acknowledge->toArray());
    }
}
