<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Controller\InternalApi;

use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category\GetUserGroupRootCategoriesAccesses;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 */
class GetUserGroupCategoriesPermissionsAction
{
    private GetUserGroupRootCategoriesAccesses $getUserGroupRootCategoriesAccesses;

    public function __construct(
        GetUserGroupRootCategoriesAccesses $getUserGroupRootCategoriesAccesses
    ) {
        $this->getUserGroupRootCategoriesAccesses = $getUserGroupRootCategoriesAccesses;
    }

    public function __invoke(string $userGroupName, Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $result = $this->getUserGroupRootCategoriesAccesses->execute($userGroupName);

        return new JsonResponse($result);
    }
}
