<?php

namespace Akeneo\Platform\Job\Infrastructure\Routing;

use Akeneo\Platform\Job\Application\LaunchJobInstance\GenerateJobExecutionUrlInterface;
use Symfony\Component\Routing\RouterInterface;

class GenerateJobExecutionUrl implements GenerateJobExecutionUrlInterface
{
    private const JOB_EXECUTION_ROUTE = 'akeneo_job_process_tracker_details';

    public function __construct(
        private RouterInterface $router
    ) {
    }

    public function fromJobExecutionId(int $jobExecutionId): string
    {
        $route = $this->router->generate(self::JOB_EXECUTION_ROUTE, ['id' => $jobExecutionId]);

        return sprintf('%s%s', '#', $route);
    }
}
