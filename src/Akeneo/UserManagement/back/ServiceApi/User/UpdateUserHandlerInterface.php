<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\UserManagement\ServiceApi\User;

use Akeneo\UserManagement\Component\Model\UserInterface;

interface UpdateUserHandlerInterface
{
    public function handle(UpdateUserCommand $updateUserCommand): UserInterface;
}
