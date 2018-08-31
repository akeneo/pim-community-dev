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

namespace Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Record;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\Record\FindRecordItemsForEnrichedEntityInterface;
use Akeneo\EnrichedEntity\Domain\Query\Record\RecordItem;
use Akeneo\EnrichedEntity\Domain\Repository\RecordRepositoryInterface;
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

    /** @var RecordRepositoryInterface  */
    private $recordRepository;

    /**
     * @param Connection $sqlConnection
     * @param RecordRepositoryInterface $recordRepository
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
        SELECT identifier, enriched_entity_identifier, code, labels
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
                $result['code'],
                $result['labels']
            );
        }

        return $recordItems;
    }

    private function hydrateRecordItem(
        string $identifier,
        string $enrichedEntityIdentifier,
        string $code,
        string $normalizedLabels
    ): RecordItem {
        $platform = $this->sqlConnection->getDatabasePlatform();

        $labels = json_decode($normalizedLabels, true);
        $identifier = Type::getType(Type::STRING)->convertToPHPValue($identifier, $platform);
        $enrichedEntityIdentifier = Type::getType(Type::STRING)
            ->convertToPHPValue($enrichedEntityIdentifier, $platform);

        $recordItem = new RecordItem();
        $recordItem->identifier = RecordIdentifier::fromString($identifier);
        $recordItem->enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString($enrichedEntityIdentifier);
        $recordItem->code = RecordCode::fromString($code);
        $recordItem->labels = LabelCollection::fromArray($labels);

        return $recordItem;
    }
}
