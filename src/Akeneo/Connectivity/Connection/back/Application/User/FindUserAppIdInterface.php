<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\User;

use Akeneo\UserManagement\Component\Model\UserInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FindUserAppIdInterface
{
    public function execute(UserInterface $user): ?string;
}
