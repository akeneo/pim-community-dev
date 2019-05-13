<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordLabelsByIdentifiersInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;

class InMemoryFindRecordLabelsByIdentifiers implements FindRecordLabelsByIdentifiersInterface
{
    /**
     * {@inheritdoc}
     */
    public function find(array $recordIdentifiers): array
    {
        throw new NotImplementedException('find');
    }
}
