<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Application\Record\SearchRecord;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\CountRecordsInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindIdentifiersForQueryInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordItemsForIdentifiersAndQueryInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\IdentifiersForQueryResult;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\ReferenceEntity\Domain\Query\Record\SearchRecordResult;

/**
 * This service takes a record search query and will return a collection of record items.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class SearchRecord
{
    public function __construct(
        private FindIdentifiersForQueryInterface $findIdentifiersForQuery,
        private FindRecordItemsForIdentifiersAndQueryInterface $findRecordItemsForIdentifiersAndQuery,
        private CountRecordsInterface $countRecords
    ) {
    }

    public function __invoke(RecordQuery $query): SearchRecordResult
    {
        /** @var IdentifiersForQueryResult $result */
        $result = $this->findIdentifiersForQuery->find($query);
        $records = $this->findRecordItemsForIdentifiersAndQuery->find($result->identifiers, $query);
        $totalCount = $this->countTotalRecords($query);

        return new SearchRecordResult($records, $result->matchesCount, $totalCount);
    }

    private function countTotalRecords(RecordQuery $recordQuery): int
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($recordQuery->getFilter('reference_entity')['value']);

        return $this->countRecords->forReferenceEntity($referenceEntityIdentifier);
    }
}
