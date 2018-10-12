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
     * The recordsKeys parameters is an arrays of records string composite keys:
     * [
     *     ['reference_entity_identifier' => 'designer', 'record_code' => 'stark',]
     *     ['reference_entity_identifier' => 'designer', 'record_code' => 'coco',]
     * ]
     *
     * @param array $recordsKeys
     */
    public function bulkRemoveByReferenceEntityIdentifiersAndCodes(array $records);
}
