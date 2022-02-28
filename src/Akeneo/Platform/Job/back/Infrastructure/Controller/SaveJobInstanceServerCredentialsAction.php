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

use Akeneo\Platform\Job\Infrastructure\Query\JobInstanceServerCredentials;
use Akeneo\Platform\Job\Infrastructure\Query\SaveJobInstanceServerCredentials;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SaveJobInstanceServerCredentialsAction
{
    public function __construct(
        private SaveJobInstanceServerCredentials $saveJobInstanceServerCredentials,
    ) {
    }

    public function __invoke(Request $request): Response {
        $jobInstanceServerCredentials = JobInstanceServerCredentials::create(json_decode($request->getContent(), true));

        $this->saveJobInstanceServerCredentials->save($jobInstanceServerCredentials);

        return new Response();
    }
}
