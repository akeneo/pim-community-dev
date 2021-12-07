<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Controller;

use Akeneo\Platform\Job\Application\SearchJobUsers\SearchJobUsersHandler;
use Akeneo\Platform\Job\Application\SearchJobUsers\SearchJobUsersQuery;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Security;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetJobUsersAction
{
    public function __construct(
        private Security $security,
        private SecurityFacade $securityFacade,
        private SearchJobUsersHandler $searchJobUsersHandler
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $this->denyAccessUnlessAclIsGranted();

        if (!$this->securityFacade->isGranted('pim_enrich_job_tracker_view_all_jobs')) {
            return new JsonResponse([$this->security->getUser()->getUserIdentifier()]);
        }

        $searchJobUsersQuery = new SearchJobUsersQuery();
        $searchJobUsersQuery->search = (string) $request->get('search', '');

        $jobUsers = $this->searchJobUsersHandler->search($searchJobUsersQuery);

        return new JsonResponse($jobUsers);
    }

    private function denyAccessUnlessAclIsGranted(): void
    {
        if (!$this->securityFacade->isGranted('pim_enrich_job_tracker_index')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to list job types.');
        }
    }
}
