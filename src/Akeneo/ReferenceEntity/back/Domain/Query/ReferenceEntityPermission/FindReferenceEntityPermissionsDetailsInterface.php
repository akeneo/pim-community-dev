<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Query\ReferenceEntityPermission;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
interface FindReferenceEntityPermissionsDetailsInterface
{
    /**
     * @return PermissionDetails[]
     */
    public function __invoke(ReferenceEntityIdentifier $referenceEntityIdentifier): array;
}
