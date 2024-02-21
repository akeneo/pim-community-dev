<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\UserManagement\ServiceApi\User;

interface UpsertUserHandlerInterface
{
    const TYPE_USER = 'user';
    const TYPE_API = 'api';
    const TYPE_JOB = 'job';

    public function handle(UpsertUserCommand $upsertUserCommand): void;
}
