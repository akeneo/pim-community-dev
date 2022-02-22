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

use Akeneo\Platform\Job\Infrastructure\Query\SaveJobInstanceSchedule;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SaveJobInstanceScheduleAction
{
    public function __construct(
        private SaveJobInstanceSchedule $saveJobInstanceSchedule,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $requestBody = json_decode($request->getContent(), true);

        $this->saveJobInstanceSchedule->save($requestBody['job_instance_code'], $requestBody['cron_expression']);

        return new Response();
    }
}
