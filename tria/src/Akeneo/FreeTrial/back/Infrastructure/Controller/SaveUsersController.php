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

use Akeneo\FreeTrial\Application\InviteUsers;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
