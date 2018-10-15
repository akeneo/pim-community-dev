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
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordItemsForIdentifiersInterface;
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

    /** @var FindRecordItemsForIdentifiersInterface */
    private $findRecordItemsForIdentifiers;

    public function __construct(FindIdentifiersForQueryInterface $findIdentifiersForQuery, FindRecordItemsForIdentifiersInterface $findRecordItemsForIdentifiers)
    {
        $this->findIdentifiersForQuery = $findIdentifiersForQuery;
        $this->findRecordItemsForIdentifiers = $findRecordItemsForIdentifiers;
    }

    public function __invoke(RecordQuery $query): SearchRecordResult
    {
        $result = ($this->findIdentifiersForQuery)($query);

        $records = ($this->findRecordItemsForIdentifiers)($result->identifiers);

        $queryResult = new SearchRecordResult();
        $queryResult->total = $result->total;
        $queryResult->items = $records;

        return $queryResult;
    }
}
