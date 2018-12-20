<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\ReferenceEntityPermission;

use Akeneo\ReferenceEntity\Domain\Model\Permission\ReferenceEntityPermission;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityPermissionRepositoryInterface;

class SqlReferenceEntityPermissionRepository implements ReferenceEntityPermissionRepositoryInterface
{
    public function save(ReferenceEntityPermission $referenceEntityPermission): void
    {
        // TODO: Implement save() method.
    }
}
