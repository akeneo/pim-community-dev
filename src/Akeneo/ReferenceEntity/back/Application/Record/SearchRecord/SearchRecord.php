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
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordsForIdentifiersInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\ReferenceEntity\Domain\Query\Record\SearchRecordResult;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class SearchRecord
{
    /** @var FindIdentifiersForQueryInterface */
    private $findIdentifiersForQuery;

    /** @var FindRecordsForIdentifiersInterface */
    private $findRecordsForIdentifiers;

    public function __construct(FindIdentifiersForQueryInterface $findIdentifiersForQuery, FindRecordsForIdentifiersInterface $findRecordsForIdentifiers)
    {
        $this->findIdentifiersForQuery = $findIdentifiersForQuery;
        $this->findRecordsForIdentifiers = $findRecordsForIdentifiers;
    }

    public function __invoke(RecordQuery $query): SearchRecordResult
    {
        $result = ($this->findIdentifiersForQuery)($query);

        $records = ($this->findRecordsForIdentifiers)($result->identifiers);

        $queryResult = new SearchRecordResult();
        $queryResult->total = $result->total;
        $queryResult->records = $records;

        return $queryResult;
    }
}
