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

use Akeneo\UserManagement\ServiceApi\User\ListUsersHandlerInterface;
use Akeneo\UserManagement\ServiceApi\User\User;
use Akeneo\UserManagement\ServiceApi\User\UsersQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetUsersAction
{
    public function __construct(
        private readonly ListUsersHandlerInterface $listUsersHandler,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $searchAfterId = $request->query->get('search_after_id');
        $search = $request->query->get('search');
        $limit = $request->query->get('limit');

        $users = \array_map(
            static fn (User $user) => ['id' => $user->getId(), 'username' => $user->getUsername()],
            $this->listUsersHandler->fromQuery(new UsersQuery(
                search: $search,
                searchAfterId: null !== $searchAfterId ? (int) $searchAfterId : null,
                limit: null !== $limit ? (int) $limit : null,
            )),
        );

        return new JsonResponse($users, Response::HTTP_OK);
    }
}
