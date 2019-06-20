<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Record\Subscribers;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;

interface IndexByReferenceEntityInBackgroundInterface
{
    public function execute(ReferenceEntityIdentifier $referenceEntityIdentifier): void;
}
