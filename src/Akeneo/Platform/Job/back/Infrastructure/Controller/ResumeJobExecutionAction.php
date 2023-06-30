<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Controller;

use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionQueueInterface;
use Akeneo\Tool\Component\BatchQueue\Queue\PausedJobExecutionMessage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ResumeJobExecutionAction
{
    public function __construct(
        private readonly JobExecutionQueueInterface $jobExecutionQueue,
    ) {
    }

    public function __invoke(Request $request, int $id): Response
    {
        $jobExecutionMessage = PausedJobExecutionMessage::createJobExecutionMessage($id, []);
        $this->jobExecutionQueue->publish($jobExecutionMessage);
        return new JsonResponse();
    }
}
