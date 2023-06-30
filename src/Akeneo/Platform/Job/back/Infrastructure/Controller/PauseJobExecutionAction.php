<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Controller;

use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Query\SqlUpdateJobExecutionStatus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class PauseJobExecutionAction
{
    public function __construct(
        private readonly SqlUpdateJobExecutionStatus $updateJobExecutionStatus,
    ) {
    }

    public function __invoke(Request $request, int $id): Response
    {
        $this->updateJobExecutionStatus->updateByJobExecutionId($id, new BatchStatus(BatchStatus::PAUSING));
        return new JsonResponse();
    }
}
