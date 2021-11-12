<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Controller;

use Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionHandler;
use Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class IndexAction
{
    private SearchJobExecutionHandler $searchJobExecutionHandler;

    public function __construct(SearchJobExecutionHandler $searchJobExecutionHandler)
    {
        $this->searchJobExecutionHandler = $searchJobExecutionHandler;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $searchJobExecutionQuery = new SearchJobExecutionQuery();
        $searchJobExecutionQuery->page = (int) $request->get('page', 1);
        $searchJobExecutionQuery->size = (int) $request->get('size', 25);
        $searchJobExecutionQuery->type = $request->get('type', []);
        $searchJobExecutionQuery->status = $request->get('status', []);

        $jobExecutionTable = $this->searchJobExecutionHandler->search($searchJobExecutionQuery);

        return new JsonResponse($jobExecutionTable->normalize());
    }
}
