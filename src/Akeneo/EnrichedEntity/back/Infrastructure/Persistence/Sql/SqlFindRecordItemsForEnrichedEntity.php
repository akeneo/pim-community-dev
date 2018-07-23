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

namespace Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\FindRecordItemsForEnrichedEntityInterface;
use Akeneo\EnrichedEntity\Domain\Query\RecordItem;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindRecordItemsForEnrichedEntity implements FindRecordItemsForEnrichedEntityInterface
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
     * {@inheritdoc}
     */
    public function __invoke(EnrichedEntityIdentifier $identifier): array
    {
        $query = <<<SQL
        SELECT identifier, enriched_entity_identifier, labels
        FROM akeneo_enriched_entity_record
        WHERE enriched_entity_identifier = :enriched_entity_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'enriched_entity_identifier' => (string) $identifier
        ]);
        $results = $statement->fetchAll();
        $statement->closeCursor();

        $recordItems = [];
        foreach ($results as $result) {
            $recordItems[] = $this->hydrateRecordItem(
                $result['identifier'],
                $result['enriched_entity_identifier'],
                $result['labels']
            );
        }

        return $recordItems;
    }

    private function hydrateRecordItem(
        string $identifier,
        string $enrichedEntityIdentifier,
        string $normalizedLabels
    ): RecordItem {
        $platform = $this->sqlConnection->getDatabasePlatform();

        $labels = json_decode($normalizedLabels, true);
        $identifier = Type::getType(Type::STRING)->convertToPHPValue($identifier, $platform);
        $enrichedEntityIdentifier = Type::getType(Type::STRING)
            ->convertToPHPValue($enrichedEntityIdentifier, $platform);

        $recordItem = new RecordItem();
        $recordItem->identifier = RecordIdentifier::from($enrichedEntityIdentifier, $identifier);
        $recordItem->enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString($enrichedEntityIdentifier);
        $recordItem->labels = LabelCollection::fromArray($labels);

        return $recordItem;
    }
}
