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

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record;

use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordItemsForIdentifiersAndQueryInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\BulkRecordItemHydrator;
use Doctrine\DBAL\Connection;

/**
 *
 * Find record items for the given record identifiers & the given record query.
 * Note that this query searches only records with the same reference entity.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindRecordItemsForIdentifiersAndQuery implements FindRecordItemsForIdentifiersAndQueryInterface
{
    /** @var Connection */
    private $sqlConnection;

    /** @var BulkRecordItemHydrator */
    private $bulkRecordItemHydrator;

    public function __construct(
        Connection $sqlConnection,
        BulkRecordItemHydrator $bulkRecordItemHydrator
    ) {
        $this->sqlConnection = $sqlConnection;
        $this->bulkRecordItemHydrator = $bulkRecordItemHydrator;
    }

    public function __invoke(array $identifiers, RecordQuery $query): array
    {
        $normalizedRecordItems = $this->fetchAll($identifiers);
        $recordItems = $this->bulkRecordItemHydrator->hydrateAll($normalizedRecordItems, $query);

        return $recordItems;
    }

    private function fetchAll(array $identifiers): array
    {
        $sqlQuery = <<<SQL
        SELECT
            record.identifier,
            record.reference_entity_identifier,
            record.code,
            record.value_collection,
            reference.attribute_as_image,
            reference.attribute_as_label
        FROM akeneo_reference_entity_record AS record
        INNER JOIN akeneo_reference_entity_reference_entity AS reference
            ON reference.identifier = record.reference_entity_identifier
        WHERE record.identifier IN (:identifiers)
        ORDER BY FIELD(record.identifier, :identifiers);
SQL;

        $statement = $this->sqlConnection->executeQuery($sqlQuery, [
            'identifiers' => $identifiers,
        ], ['identifiers' => Connection::PARAM_STR_ARRAY]);
        $results = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return $results;
    }
}
