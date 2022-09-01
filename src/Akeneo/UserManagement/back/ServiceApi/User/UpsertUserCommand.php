<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\UserManagement\ServiceApi\User;

use Akeneo\UserManagement\Component\Model\User;

final class UpsertUserCommand
{
    private function __construct(
        public string $username,
        public string $password,
        public string $email,
        public string $type,
        public string $firstName,
        public string $lastName,
        public array $roleCodes,
        public array $groupIds = [],
    ) {
    }

    /**
     * @param string[] $roleCodes
     * @param string[] $groupIds
     */
    public static function api(
        string $username,
        string $password,
        string $email,
        string $firstName,
        string $lastName,
        array $roleCodes,
        array $groupIds = [],
    ): self {
        return new self(
            $username,
            $password,
            $email,
            User::TYPE_API,
            $firstName,
            $lastName,
            $roleCodes,
            $groupIds,
        );
    }

    /**
     * @param string[] $roleCodes
     * @param string[] $groupIds
     */
    public static function job(
        string $username,
        string $password,
        string $email,
        string $firstName,
        string $lastName,
        array $roleCodes,
        array $groupIds = [],
    ): self {
        return new self(
            $username,
            $password,
            $email,
            User::TYPE_JOB,
            $firstName,
            $lastName,
            $roleCodes,
            $groupIds,
        );
    }

    /**
     * @param string[] $roleCodes
     * @param string[] $groupIds
     */
    public static function user(
        string $username,
        string $password,
        string $email,
        string $firstName,
        string $lastName,
        array $roleCodes,
        array $groupIds = [],
    ): self {
        return new self(
            $username,
            $password,
            $email,
            User::TYPE_USER,
            $firstName,
            $lastName,
            $roleCodes,
            $groupIds,
        );
    }
}
