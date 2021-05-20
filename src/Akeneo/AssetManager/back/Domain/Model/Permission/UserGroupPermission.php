<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Domain\Model\Permission;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class UserGroupPermission
{
    private const USER_GROUP_IDENTIFIER = 'user_group_identifier';
    private const RIGHT_LEVEL = 'right_level';

    private UserGroupIdentifier $userGroupIdentifier;

    private RightLevel $rightLevel;

    private function __construct(
        UserGroupIdentifier $userGroupIdentifier,
        RightLevel $rightLevel
    ) {
        $this->userGroupIdentifier = $userGroupIdentifier;
        $this->rightLevel = $rightLevel;
    }

    public static function create(
        UserGroupIdentifier $userGroupIdentifier,
        RightLevel $rightLevel
    ) {
        return new self($userGroupIdentifier, $rightLevel);
    }

    public function normalize(): array
    {
        return [
            self::USER_GROUP_IDENTIFIER => $this->userGroupIdentifier->normalize(),
            self::RIGHT_LEVEL => $this->rightLevel->normalize(),
        ];
    }

    public function getUserGroupIdentifier(): UserGroupIdentifier
    {
        return $this->userGroupIdentifier;
    }

    public function isAllowedToEdit(): bool
    {
        return $this->rightLevel->equals(RightLevel::edit());
    }
}
