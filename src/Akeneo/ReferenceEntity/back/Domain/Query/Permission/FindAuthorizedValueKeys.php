<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Query\Permission;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKeyCollection;

interface FindAuthorizedValueKeys
{
    public function __invoke(
        UserIdentifier $userIdentifier,
        ReferenceEntityIdentifier $referenceEntityIdentifier
    ): ValueKeyCollection;
}
