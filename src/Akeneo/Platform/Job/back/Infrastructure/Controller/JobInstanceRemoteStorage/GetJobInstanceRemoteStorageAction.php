<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Job\Infrastructure\Controller\JobInstanceRemoteStorage;

use Akeneo\Platform\Job\Infrastructure\Query\JobInstanceRemoteStorage\GetJobInstanceRemoteStorage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetJobInstanceRemoteStorageAction
{
    public function __construct(
        private GetJobInstanceRemoteStorage $getJobInstanceRemoteStorage,
    ) {
    }

    public function __invoke(Request $request): Response {
        $jobInstanceCode = $request->get('job_instance_code');
        $jobInstanceRemoteStorage = $this->getJobInstanceRemoteStorage->byJobInstanceCode($jobInstanceCode);

        return new JsonResponse($jobInstanceRemoteStorage ? $jobInstanceRemoteStorage->normalize() : []);
    }
}
