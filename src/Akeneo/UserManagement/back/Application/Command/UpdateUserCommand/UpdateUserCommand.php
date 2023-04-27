<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\UserManagement\Application\Command\UpdateUserCommand;

use Akeneo\UserManagement\Component\Model\UserInterface;

final class UpdateUserCommand
{
    /**
     * @param array $data
     */
    public function __construct(
        public UserInterface $user,
        public array $data,
    ) {
        // unset common keys
    }
}
