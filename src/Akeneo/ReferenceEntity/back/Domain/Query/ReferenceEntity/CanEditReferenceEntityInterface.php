<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\PrincipalIdentifier;

interface CanEditReferenceEntityInterface
{
    public function __invoke(PrincipalIdentifier $principalIdentifier, ReferenceEntityIdentifier $referenceEntityIdentifier): bool;
}
