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

namespace Akeneo\Platform\Installer\Infrastructure\Controller;

use Akeneo\Platform\Installer\Application\ResetDatabase\ResetDatabaseCommand;
use Akeneo\Platform\Installer\Application\ResetDatabase\ResetDatabaseHandler;
use Symfony\Component\HttpFoundation\JsonResponse;

class ResetInstanceAction
{
    public function __construct(private readonly ResetDatabaseHandler $resetDatabaseHandler)
    {
    }

    public function __invoke(): JsonResponse
    {
        $this->resetDatabaseHandler->handle(new ResetDatabaseCommand([]));

        return new JsonResponse();
    }
}
