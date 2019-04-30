<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\Test\Acceptance\Common\NotImplementedException;

class InMemoryFindRecordLinkValueKeys
{
    public function fetch(ReferenceEntityIdentifier $referenceEntityIdentifier)
    {
        throw new NotImplementedException('fetch');
    }
}
