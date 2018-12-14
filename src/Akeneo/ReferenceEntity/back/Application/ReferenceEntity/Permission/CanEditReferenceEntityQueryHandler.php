<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\ReferenceEntity\Permission;

use Akeneo\ReferenceEntity\Domain\Model\PrincipalIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\CanEditReferenceEntityInterface;

/**
 * Query handler that determines wether a editing the reference entity for a principal id is authorized.
 *
 * The checks of the ACL (Access Control List) is done separately (usually in the adapters). The ACL check may be done
 * in this service in the future.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CanEditReferenceEntityQueryHandler
{
    /** @var CanEditReferenceEntityInterface */
    private $canEditReferenceEntity;

    public function __construct(CanEditReferenceEntityInterface $canEditReferenceEntity)
    {
        $this->canEditReferenceEntity = $canEditReferenceEntity;
    }

    public function __invoke(CanEditReferenceEntityQuery $query): bool
    {
        return ($this->canEditReferenceEntity)(
            PrincipalIdentifier::fromString($query->principalIdentifier),
            ReferenceEntityIdentifier::fromString($query->referenceEntityIdentifier)
        );
    }
}
