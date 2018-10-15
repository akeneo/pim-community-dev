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

use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordItemsForIdentifiersInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordItem;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\RecordIndexer;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindRecordItemsForIdentifiers implements FindRecordItemsForIdentifiersInterface
{
    /** @var Connection */
    private $sqlConnection;

    /**
     * @param Connection $sqlConnection
     */
    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    /**
     * @return string[]
     */
    public function __invoke(array $identifiers): array
    {
        $sqlQuery = <<<SQL
        SELECT ee.identifier, ee.reference_entity_identifier, ee.code, ee.labels, fi.image, ee.value_collection
        FROM akeneo_reference_entity_record AS ee
        LEFT JOIN (
          SELECT file_key, JSON_OBJECT("file_key", file_key, "original_filename", original_filename) as image
          FROM akeneo_file_storage_file_info
        ) AS fi ON fi.file_key = ee.image
        WHERE identifier IN (:identifiers);
SQL;

        $statement = $this->sqlConnection->executeQuery($sqlQuery, [
            'identifiers' => $identifiers
        ], ['identifiers' => Connection::PARAM_STR_ARRAY]);
        $results = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $recordItems = [];
        foreach ($results as $result) {
            $image = null !== $result['image'] ? json_decode($result['image'], true) : null;
            $recordItems[] = $this->hydrateRecordItem(
                $result['identifier'],
                $result['reference_entity_identifier'],
                $result['code'],
                $image,
                $result['labels'],
                json_decode($result['value_collection'], true)
            );
        }

        return $recordItems;
    }

    private function hydrateRecordItem(
        string $identifier,
        string $referenceEntityIdentifier,
        string $code,
        ?array $image,
        string $normalizedLabels,
        array $values
    ): RecordItem {
        $platform = $this->sqlConnection->getDatabasePlatform();

        $labels = json_decode($normalizedLabels, true);
        $identifier = Type::getType(Type::STRING)->convertToPHPValue($identifier, $platform);
        $referenceEntityIdentifier = Type::getType(Type::STRING)
            ->convertToPHPValue($referenceEntityIdentifier, $platform);
        $code = Type::getType(Type::STRING)->convertToPHPValue($code, $platform);

        $recordItem = new RecordItem();
        $recordItem->identifier = $identifier;
        $recordItem->referenceEntityIdentifier = $referenceEntityIdentifier;
        $recordItem->code = $code;
        $recordItem->labels = $labels;
        $recordItem->image = $image;
        $recordItem->values = $values;

        return $recordItem;
    }
}
