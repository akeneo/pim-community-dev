<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\UserManagement\ServiceApi\User;

final class DeleteUserCommand
{
    private function __construct(
        public string $username,
    ) {
    }

    public static function delete(
        string $username,
    ): self {
        return new self(
            $username,
        );
    }
}
