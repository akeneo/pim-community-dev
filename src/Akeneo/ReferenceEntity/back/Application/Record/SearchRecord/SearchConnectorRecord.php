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

use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\FindConnectorRecordsByIdentifiersInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindIdentifiersForQueryInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;

/**
 * This service takes a record search query and will return a list of connector-records.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SearchConnectorRecord
{
    public function __construct(
        private FindIdentifiersForQueryInterface $findIdentifiersForQuery,
        private FindConnectorRecordsByIdentifiersInterface $findConnectorRecordsByIdentifiers
    ) {
    }

    public function __invoke(RecordQuery $query): SearchConnectorRecordResult
    {
        $result = $this->findIdentifiersForQuery->find($query);
        $records = $this->findConnectorRecordsByIdentifiers->find($result->identifiers, $query);

        return SearchConnectorRecordResult::createFromSearchAfterQuery($records, $result->lastSortValue);
    }
}
