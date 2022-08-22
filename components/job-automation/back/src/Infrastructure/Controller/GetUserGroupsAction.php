<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\JobAutomation\Infrastructure\Controller;

use Akeneo\UserManagement\ServiceApi\UserGroup\ListUserGroupInterface;
use Akeneo\UserManagement\ServiceApi\UserGroup\UserGroup;
use Akeneo\UserManagement\ServiceApi\UserGroup\UserGroupQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetUserGroupsAction
{
    public function __construct(
        private ListUserGroupInterface $listUserGroup,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $userGroups = array_map(
            static fn (UserGroup $userGroup) => $userGroup->getLabel(),
            $this->listUserGroup->fromQuery(new UserGroupQuery()),
        );

        return new JsonResponse($userGroups, Response::HTTP_OK);
    }
}
