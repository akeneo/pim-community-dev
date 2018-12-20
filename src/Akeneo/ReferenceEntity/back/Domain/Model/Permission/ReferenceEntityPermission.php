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

        $this->referenceEntityIdentifier = $referenceEntityIdentifier;
        $this->permissions = $permissions;
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
            self::PERMISSIONS => array_map(function (UserGroupPermission $userGroupPermission) {
                return $userGroupPermission->normalize();
            }, $this->permissions),
        ];
    }

    public function getReferenceEntityIdentifier(): ReferenceEntityIdentifier
    {
        return $this->referenceEntityIdentifier;
    }
}
