<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Controller;

use Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionHandler;
use Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionQuery;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class IndexAction
{
    private SearchJobExecutionHandler $searchJobExecutionHandler;
    private SecurityFacade $securityFacade;

    public function __construct(SearchJobExecutionHandler $searchJobExecutionHandler, SecurityFacade $securityFacade)
    {
        $this->searchJobExecutionHandler = $searchJobExecutionHandler;
        $this->securityFacade = $securityFacade;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $this->denyAccessUnlessAclIsGranted();

        $searchJobExecutionQuery = new SearchJobExecutionQuery();
        $searchJobExecutionQuery->page = (int) $request->get('page', 1);
        $searchJobExecutionQuery->size = (int) $request->get('size', 25);
        $sort = $request->get('sort');
        $searchJobExecutionQuery->sortColumn = $sort['column'] ?? 'started_at';
        $searchJobExecutionQuery->sortDirection = $sort['direction'] ?? 'DESC';
        $searchJobExecutionQuery->type = $request->get('type', []);
        $searchJobExecutionQuery->status = $request->get('status', []);
        $searchJobExecutionQuery->search = $request->get('search', '');

        $jobExecutionTable = $this->searchJobExecutionHandler->search($searchJobExecutionQuery);

        return new JsonResponse($jobExecutionTable->normalize());
    }

    private function denyAccessUnlessAclIsGranted()
    {
        if (!$this->securityFacade->isGranted('pim_enrich_job_tracker_index')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to list jobs.');
        }
    }
}
