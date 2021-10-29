<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Controller;

use Akeneo\Platform\Job\Application\SearchJobExecutionTable\SearchExecutionTableQuery;
use Akeneo\Platform\Job\Application\SearchJobExecutionTable\SearchJobExecutionTable;
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
    private SearchJobExecutionTable $searchJobExecutionTable;

    public function __construct(SearchJobExecutionTable $searchJobExecutionTable)
    {
        $this->searchJobExecutionTable = $searchJobExecutionTable;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $searchJobExecutionTableQuery = new SearchExecutionTableQuery();
        $searchJobExecutionTableResult = $this->searchJobExecutionTable->search($searchJobExecutionTableQuery);

        return new JsonResponse(
            $searchJobExecutionTableResult->normalize()
        );
    }
}
