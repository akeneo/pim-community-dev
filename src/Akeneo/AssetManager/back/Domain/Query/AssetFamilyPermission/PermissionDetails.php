<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Query\ReferenceEntityPermission;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class PermissionDetails
{
    /** @var int */
    public $userGroupIdentifier;

    /** @var string */
    public $userGroupName;

    /** @var string */
    public $rightLevel;

    public function normalize()
    {
        return [
            'user_group_identifier' => $this->userGroupIdentifier,
            'user_group_name'       => $this->userGroupName,
            'right_level'           => $this->rightLevel,
        ];
    }
}
