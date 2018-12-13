<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Permission;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\UserIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Permission\CanEditReferenceEntityInterface;

/**
 * Query handler that determines wether a an action is authorized.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class PermissionQueryHandler
{
    /** @var CanEditReferenceEntityInterface */
    private $canEditReferenceEntity;

    public function __construct(CanEditReferenceEntityInterface $canEditReferenceEntity)
    {
        $this->canEditReferenceEntity = $canEditReferenceEntity;
    }

    public function __invoke(PermissionCheckQuery $permissionCheckQuery): bool
    {
        if ($permissionCheckQuery instanceof CanEditReferenceEntityQuery) {
            return ($this->canEditReferenceEntity)(
                UserIdentifier::fromString($permissionCheckQuery->userIdentifier),
                ReferenceEntityIdentifier::fromString($permissionCheckQuery->referenceEntityIdentifier)
            );
        }

        if ($permissionCheckQuery instanceof CanEditValueQuery) {
            return ($this->canEditReferenceEntity)(
                    UserIdentifier::fromString($permissionCheckQuery->userIdentifier),
                    ReferenceEntityIdentifier::fromString($permissionCheckQuery->referenceEntityIdentifier)
                )
                // && ($this->canEditLocale($permissionCheckQuery->userIdentifier, $permissionCheckQuery->localeIdentifier);
            ;

        }

        throw new UnsupportedPermissionQueryException($permissionCheckQuery);
    }
}
