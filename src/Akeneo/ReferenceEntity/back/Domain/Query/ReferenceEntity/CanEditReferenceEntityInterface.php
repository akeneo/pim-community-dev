<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\SecurityIdentifier;

interface CanEditReferenceEntityInterface
{
    public function __invoke(SecurityIdentifier $principalIdentifier, ReferenceEntityIdentifier $referenceEntityIdentifier): bool;
}
