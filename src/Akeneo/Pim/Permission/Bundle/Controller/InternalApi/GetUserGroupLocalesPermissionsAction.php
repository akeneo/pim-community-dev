<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Controller\InternalApi;

use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Locale\GetUserGroupLocalesAccesses;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 */
class GetUserGroupLocalesPermissionsAction
{
    private GetUserGroupLocalesAccesses $getUserGroupLocalesAccesses;

    public function __construct(
        GetUserGroupLocalesAccesses $getUserGroupLocalesAccesses
    ) {
        $this->getUserGroupLocalesAccesses = $getUserGroupLocalesAccesses;
    }

    public function __invoke(string $userGroupName, Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $result = $this->getUserGroupLocalesAccesses->execute($userGroupName);

        return new JsonResponse($result);
    }
}
