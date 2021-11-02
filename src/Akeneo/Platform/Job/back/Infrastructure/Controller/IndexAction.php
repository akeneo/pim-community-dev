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
    private SearchJobExecutionHandler $searchJobExecutionTable;

    public function __construct(SearchJobExecutionHandler $searchJobExecutionTable)
    {
        $this->searchJobExecutionTable = $searchJobExecutionTable;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $searchJobExecutionTableQuery = new SearchJobExecutionQuery();
        $searchJobExecutionTableQuery->page = $request->query->getInt('page', 1);

        $searchJobExecutionTableResult = $this->searchJobExecutionTable->search($searchJobExecutionTableQuery);

        return new JsonResponse(
            $searchJobExecutionTableResult->normalize()
        );
    }
}
