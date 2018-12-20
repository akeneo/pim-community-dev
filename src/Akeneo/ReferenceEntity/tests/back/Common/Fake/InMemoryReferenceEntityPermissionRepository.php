<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Model\Permission\ReferenceEntityPermission;
use Akeneo\ReferenceEntity\Domain\Model\Permission\RightLevel;
use Akeneo\ReferenceEntity\Domain\Model\Permission\UserGroupIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityPermissionRepositoryInterface;

class InMemoryReferenceEntityPermissionRepository implements ReferenceEntityPermissionRepositoryInterface
{
    /** @var ReferenceEntityPermission[] */
    private $referenceEntityPermissions;

    public function save(ReferenceEntityPermission $referenceEntityPermission): void
    {
        $refEntityIdentifier = $referenceEntityPermission->getReferenceEntityIdentifier()->normalize();
        $this->referenceEntityPermissions[$refEntityIdentifier] = $referenceEntityPermission;
    }

    public function hasPermission(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        UserGroupIdentifier $userGroupIdentifier,
        RightLevel $rightLevel
    ): bool {
        $refEntityIdentifier = $referenceEntityIdentifier->normalize();

        if (key_exists($refEntityIdentifier, $this->referenceEntityPermissions)) {
            $referenceEntityPermission = $this->referenceEntityPermissions[$refEntityIdentifier];
            $normalized = $referenceEntityPermission->normalize();

            foreach ($normalized['permissions'] as $normalizedPermission) {
                if (
                    $normalizedPermission['user_group_identifier'] === $userGroupIdentifier->normalize()
                    && $normalizedPermission['right_level'] === $rightLevel->normalize()
                ) {
                    return true;
                }
            }
        }

        return false;
    }
}
