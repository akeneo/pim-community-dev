<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\CanEditReferenceEntity;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\SecurityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\UserGroup\FindUserGroupsForSecurityIdentifierInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityPermissionRepositoryInterface;

/**
 * Query handler that determines wether a editing the reference entity for a principal id is authorized.
 *
 * The checks of the ACL (Access Control List) is done separately (usually in the adapters).
 * The ACL check may be done in this service in the future.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CanEditReferenceEntityQueryHandler
{
    /** @var ReferenceEntityPermissionRepositoryInterface */
    private $referenceEntityPermissionRepository;

    /** @var FindUserGroupsForSecurityIdentifierInterface */
    private $findUserGroupsForSecurityIdentifier;

    public function __construct(
        ReferenceEntityPermissionRepositoryInterface $referenceEntityPermissionRepository,
        FindUserGroupsForSecurityIdentifierInterface $findUserGroupsForSecurityIdentifier
    ) {
        $this->referenceEntityPermissionRepository = $referenceEntityPermissionRepository;
        $this->findUserGroupsForSecurityIdentifier = $findUserGroupsForSecurityIdentifier;
    }

    public function __invoke(CanEditReferenceEntityQuery $query): bool
    {
        $referenceEntityPermission = $this->referenceEntityPermissionRepository->getByReferenceEntityIdentifier(
            ReferenceEntityIdentifier::fromString($query->referenceEntityIdentifier)
        );
        $userGroupIdentifiers = ($this->findUserGroupsForSecurityIdentifier)(
            SecurityIdentifier::fromString($query->securityIdentifier)
        );

        return $referenceEntityPermission->isAllowedToEdit($userGroupIdentifiers);
    }
}
