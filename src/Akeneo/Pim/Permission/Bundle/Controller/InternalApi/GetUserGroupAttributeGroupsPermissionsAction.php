<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Controller\InternalApi;

use Akeneo\Pim\Permission\Bundle\Persistence\ORM\AttributeGroup\GetUserGroupAttributeGroupsAccesses;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 */
class GetUserGroupAttributeGroupsPermissionsAction
{
    private GetUserGroupAttributeGroupsAccesses $getUserGroupAttributeGroupsAccesses;

    public function __construct(
        GetUserGroupAttributeGroupsAccesses $getUserGroupAttributeGroupsAccesses
    ) {
        $this->getUserGroupAttributeGroupsAccesses = $getUserGroupAttributeGroupsAccesses;
    }

    public function __invoke(string $userGroupName, Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $result = $this->getUserGroupAttributeGroupsAccesses->execute($userGroupName);

        return new JsonResponse($result);
    }
}
