<?php

namespace Akeneo\Platform\Job\Infrastructure\Controller;

use Akeneo\Platform\Job\Domain\Query\FindJobUsersInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetJobUsersAction
{
    private FindJobUsersInterface $findJobUsers;

    public function __construct(FindJobUsersInterface $findJobUsers)
    {
        $this->findJobUsers = $findJobUsers;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $page = (int) $request->get('page', 1);

        $jobUsers = $this->findJobUsers->visible($page);

        return new JsonResponse($jobUsers);
    }
}
