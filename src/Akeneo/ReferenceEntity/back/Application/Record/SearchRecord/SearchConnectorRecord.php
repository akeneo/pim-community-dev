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
    /** @var FindIdentifiersForQueryInterface */
    private $findIdentifiersForQuery;

    /** @var FindConnectorRecordsByIdentifiersInterface */
    private $findConnectorRecordsByIdentifiers;

    public function __construct(
        FindIdentifiersForQueryInterface $findIdentifiersForQuery,
        FindConnectorRecordsByIdentifiersInterface $findConnectorRecordsByIdentifiers
    ) {
        $this->findIdentifiersForQuery = $findIdentifiersForQuery;
        $this->findConnectorRecordsByIdentifiers = $findConnectorRecordsByIdentifiers;
    }

    public function __invoke(RecordQuery $query): array
    {
        $result = $this->findIdentifiersForQuery->find($query);
        $records = empty($result) ? [] : $this->findConnectorRecordsByIdentifiers->find($result->identifiers, $query);

        return $records;
    }
}
