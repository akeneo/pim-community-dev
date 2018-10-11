<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch;

use Akeneo\ReferenceEntity\Domain\Model\Record\Record;

interface RecordIndexerInterface
{
    /**
     * Indexes multiple records
     *
     * @param Record[] $records
     */
    public function bulkIndex(array $records);

    /**
     * Remove multiple records
     *
     * @param Record[] $records
     */
    public function bulkRemove(array $records);
}
