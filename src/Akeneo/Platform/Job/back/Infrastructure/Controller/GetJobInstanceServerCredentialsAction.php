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

namespace Akeneo\Platform\Job\Infrastructure\Controller;

use Akeneo\Platform\Job\Infrastructure\Query\GetJobInstanceServerCredentials;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetJobInstanceServerCredentialsAction
{
    public function __construct(
        private GetJobInstanceServerCredentials $getJobInstanceServerCredentials,
    ) {
    }

    public function __invoke(Request $request): Response {
        $jobInstanceCode = $request->get('job_instance_code');
        $jobInstanceServerCredentials = $this->getJobInstanceServerCredentials->byJobInstanceCode($jobInstanceCode);

        return new JsonResponse($jobInstanceServerCredentials ? $jobInstanceServerCredentials->normalize() : []);
    }
}
