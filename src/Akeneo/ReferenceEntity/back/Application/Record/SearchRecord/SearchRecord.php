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
    /** @var FindIdentifiersForQueryInterface */
    private $findIdentifiersForQuery;

    /** @var FindRecordItemsForIdentifiersAndQueryInterface */
    private $findRecordItemsForIdentifiersAndQuery;

    public function __construct(
        FindIdentifiersForQueryInterface $findIdentifiersForQuery,
        FindRecordItemsForIdentifiersAndQueryInterface $findRecordItemsForIdentifiersAndQuery
    ) {
        $this->findIdentifiersForQuery = $findIdentifiersForQuery;
        $this->findRecordItemsForIdentifiersAndQuery = $findRecordItemsForIdentifiersAndQuery;
    }

    public function __invoke(RecordQuery $query): SearchRecordResult
    {
        /** @var IdentifiersForQueryResult $result */
        $result = ($this->findIdentifiersForQuery)($query);
        $records = ($this->findRecordItemsForIdentifiersAndQuery)($result->identifiers, $query);
        $totalCount = 0;
        $queryResult = new SearchRecordResult($records, $result->matchesCount, $totalCount);

        return $queryResult;
    }
}
