<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\User;

use Akeneo\Connectivity\Connection\Application\User\FindUserAppIdInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindUserAppId implements FindUserAppIdInterface
{
    /**
     * @inheritdoc
     */
    public function execute(UserInterface $user): ?string
    {
        return $user->getProperty('app_id');
    }
}
