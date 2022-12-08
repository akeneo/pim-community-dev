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
        private readonly ListUserGroupInterface $listUserGroup,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $searchAfterId = $request->query->get('search_after_id');
        $searchName = $request->query->get('search_name');
        $limit = $request->query->get('limit');

        $userGroups = array_map(
            static fn (UserGroup $userGroup) => ['id' => $userGroup->getId(), 'label' => $userGroup->getLabel()],
            $this->listUserGroup->fromQuery(new UserGroupQuery(
                searchName: $searchName,
                searchAfterId: null !== $searchAfterId ? (int) $searchAfterId : null,
                limit: null !== $limit ? (int) $limit : null,
            )),
        );

        return new JsonResponse($userGroups, Response::HTTP_OK);
    }
}
