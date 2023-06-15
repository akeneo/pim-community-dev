<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\Controller;

use Akeneo\Platform\Job\ServiceApi\JobExecution\FindQueuedAndRunningJobExecutionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CheckInstanceCanBeResetAction
{
    public function __construct(
        private readonly FindQueuedAndRunningJobExecutionInterface $findQueuedAndRunningJobExecution,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $count = $this->findQueuedAndRunningJobExecution->count(1);
        $status = 0 === $count ? Response::HTTP_NO_CONTENT : Response::HTTP_BAD_REQUEST;

        return new JsonResponse(null, $status);
    }
}
