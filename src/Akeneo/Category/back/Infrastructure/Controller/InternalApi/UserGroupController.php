<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Controller\InternalApi;

use Akeneo\UserManagement\ServiceApi\UserGroup\ListUserGroupInterface;
use Akeneo\UserManagement\ServiceApi\UserGroup\UserGroup;
use Akeneo\UserManagement\ServiceApi\UserGroup\UserGroupQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserGroupController
{
    public function __construct(
        private readonly ListUserGroupInterface $listUserGroup,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $userGroups = array_map(
            static fn (UserGroup $userGroup) => ['id' => $userGroup->getId(), 'label' => $userGroup->getLabel()],
            $this->listUserGroup->fromQuery(new UserGroupQuery(limit: 1000)),
        );

        return new JsonResponse($userGroups, Response::HTTP_OK);
    }
}
