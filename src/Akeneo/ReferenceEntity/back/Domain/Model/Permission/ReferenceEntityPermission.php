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

namespace Akeneo\ReferenceEntity\Domain\Model\Permission;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ReferenceEntityPermission
{
    private const REFERENCE_ENTITY_IDENTIFIER = 'reference_entity_identifier';
    private const PERMISSIONS = 'permissions';

    /** @var ReferenceEntityIdentifier */
    private $referenceEntityIdentifier;

    /** @var UserGroupPermission[] */
    private $permissions;

    private function __construct(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        array $permissions
    ) {
        Assert::allIsInstanceOf($permissions, UserGroupPermission::class);
        $this->assertUniquePermissions($permissions);

        $this->referenceEntityIdentifier = $referenceEntityIdentifier;
        $this->permissions = $permissions;
    }

    /**
     * @param UserGroupPermission $permissions
     */
    private function assertUniquePermissions(array $permissions): void
    {
        $userGroup = [];
        foreach ($permissions as $permission) {
            $userGroupIdentifier = $permission->getUserGroupIdentifier()->normalize();
            if (in_array($userGroupIdentifier, $userGroup)) {
                throw new \InvalidArgumentException(
                    sprintf('Permission for user group %s already exists', $userGroupIdentifier)
                );
            }
            $userGroup[] = $userGroupIdentifier;
        }
    }

    /**
     * @param ReferenceEntityIdentifier $referenceEntityIdentifier
     * @param UserGroupPermission[]     $permissions
     */
    public static function create(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        array $permissions
    ): self {
        return new self($referenceEntityIdentifier, $permissions);
    }

    public function normalize(): array
    {
        return [
            self::REFERENCE_ENTITY_IDENTIFIER => $this->referenceEntityIdentifier->normalize(),
            self::PERMISSIONS                 => array_map(function (UserGroupPermission $userGroupPermission) {
                return $userGroupPermission->normalize();
            }, $this->permissions),
        ];
    }

    public function getReferenceEntityIdentifier(): ReferenceEntityIdentifier
    {
        return $this->referenceEntityIdentifier;
    }

    /**
     * @return UserGroupPermission[]
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * @param UserGroupIdentifier $userGroupIdentifiers
     */
    public function isAllowedToEdit(array $userGroupIdentifiers): bool
    {
        Assert::allIsInstanceOf($userGroupIdentifiers, UserGroupIdentifier::class);

        if (empty($this->permissions)) {
            return true;
        }
        $userGroupPermissions = $this->findUserGroupPermissions($userGroupIdentifiers);

        return $this->hasEditPermission($userGroupPermissions);
    }

    /**
     * @param UserGroupIdentifier $userGroupIdentifiers
     *
     * @return UserGroupPermission[]
     */
    private function findUserGroupPermissions(array $userGroupIdentifiers): array
    {
        $userGroupPermissions = array_filter(
            $this->permissions,
            function (UserGroupPermission $userGroupPermission) use ($userGroupIdentifiers) {
                foreach ($userGroupIdentifiers as $userGroupIdentifier) {
                    if ($userGroupPermission->getUserGroupIdentifier()->equals($userGroupIdentifier)) {
                        return true;
                    }
                }

                return false;
            }
        );

        return $userGroupPermissions;
    }

    /**
     * @param UserGroupPermission[] $userGroupPermissions
     */
    private function hasEditPermission($userGroupPermissions): bool
    {
        $editPermissions = array_filter(
            $userGroupPermissions,
            function (UserGroupPermission $userGroupPermission) {
                return $userGroupPermission->isAllowedToEdit();
            }
        );

        return 0 !== \count($editPermissions);
    }
}
