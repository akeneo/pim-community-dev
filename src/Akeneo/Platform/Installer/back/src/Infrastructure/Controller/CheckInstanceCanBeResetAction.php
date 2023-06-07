<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\Controller;

use Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionInterface;
use Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CheckInstanceCanBeResetAction
{
    public function __construct(
        //TODO service api in Job
        private readonly SearchJobExecutionInterface $searchJobExecution,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $query = new SearchJobExecutionQuery();
        $query->size = 1;
        $query->status = ['STARTING', 'IN_PROGRESS'];

        $queuedAndRunningJobExecutions = $this->searchJobExecution->search($query);

        $status = empty($queuedAndRunningJobExecutions) ? Response::HTTP_NO_CONTENT : Response::HTTP_BAD_REQUEST;

        return new JsonResponse(null, $status);
    }
}
