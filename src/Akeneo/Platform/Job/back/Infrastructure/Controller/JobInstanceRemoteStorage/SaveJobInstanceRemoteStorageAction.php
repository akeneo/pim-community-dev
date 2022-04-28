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

use Akeneo\Platform\Job\Infrastructure\Query\JobInstanceRemoteStorage\JobInstanceRemoteStorage;
use Akeneo\Platform\Job\Infrastructure\Query\JobInstanceRemoteStorage\SaveJobInstanceRemoteStorage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SaveJobInstanceRemoteStorageAction
{
    public function __construct(
        private SaveJobInstanceRemoteStorage $saveJobInstanceRemoteStorage,
    ) {
    }

    public function __invoke(Request $request): Response {
        $jobInstanceRemoteStorage = JobInstanceRemoteStorage::create(json_decode($request->getContent(), true));

        $this->saveJobInstanceRemoteStorage->save($jobInstanceRemoteStorage);

        return new Response();
    }
}
