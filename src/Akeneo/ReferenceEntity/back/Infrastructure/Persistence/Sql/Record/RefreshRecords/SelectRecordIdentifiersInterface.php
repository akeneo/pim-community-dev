<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\back\Infrastructure\Persistence\Sql\Record\RefreshRecords;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;

interface SelectRecordIdentifiersInterface
{
    /**
     * @return RecordIdentifier[]
     */
    public function fetch(): \Iterator;
}
