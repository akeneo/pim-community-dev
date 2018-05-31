<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\back\Infrastructure\Persistence\Sql;

use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\back\Domain\Repository\EnrichedEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlEnrichedEntityRepository implements EnrichedEntityRepository
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
     * @param EnrichedEntity $enrichedEntity
     */
    public function add(EnrichedEntity $enrichedEntity): void
    {
        $serializedLabels = $this->getSerializedLabels($enrichedEntity);
        $insert = <<<SQL
        REPLACE INTO akeneo_enriched_entity_enriched_entity (identifier, labels) VALUES (:identifier, :labels);
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $insert,
            [
                'identifier' => (string) $enrichedEntity->getIdentifier(),
                'labels' => $serializedLabels
            ]
        );

        if ($statement->rowCount() !== 1) {
            throw new \LogicException(
                sprintf('Expected to add one enriched entity. "%d" added', $statement->rowCount())
            );
        }
    }


    public function update(EnrichedEntity $enrichedEntity): void
    {
        // TODO: Implement update() method.
    }

    /**
     * @param EnrichedEntityIdentifier $identifier
     *
     * @return EnrichedEntity
     */
    public function findOneByIdentifier(EnrichedEntityIdentifier $identifier): ?EnrichedEntity
    {
        $fetch = <<<SQL
        SELECT identifier, labels
        FROM akeneo_enriched_entity_enriched_entity
        WHERE identifier = :identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $fetch,
            ['identifier' => (string) $identifier]
        );
        $result = $statement->fetch();

        if (!$result) {
            return null;
        }

        return $this->hidrateEnrichedEntity($result['identifier'], $result['labels']);
    }

    /**
     * @return EnrichedEntity[]
     */
    public function all(): array
    {
        $selectAllQuery = <<<SQL
        SELECT identifier, labels
        FROM akeneo_enriched_entity_enriched_entity;
SQL;
        $statement = $this->sqlConnection->executeQuery($selectAllQuery);
        $results = $statement->fetchAll();

        $enrichedEntities = [];
        foreach ($results as $result) {
            $enrichedEntities[] = $this->hidrateEnrichedEntity($result['identifier'], $result['labels']);
        }

        return $enrichedEntities;
    }

    /**
     * @param string $identifier
     *
     * @return EnrichedEntity
     */
    private function hidrateEnrichedEntity(string $identifier, string $normalizedLabels): EnrichedEntity
    {
        $platform = $this->sqlConnection->getDatabasePlatform();

        $labels = json_decode($normalizedLabels, true);
        $identifier = Type::getType(Type::STRING)->convertToPhpValue($identifier, $platform);

        $enrichedEntity = EnrichedEntity::create(
            EnrichedEntityIdentifier::fromString(
                $identifier
            ),
            $labels
        );

        return $enrichedEntity;
    }

    /**
     * @param EnrichedEntity $enrichedEntity
     *
     * @return array
     */
    private function getSerializedLabels(EnrichedEntity $enrichedEntity): string
    {
        $labels = [];
        foreach ($enrichedEntity->getLabelCodes() as $localeCode) {
            $labels[$localeCode] = $enrichedEntity->getLabel($localeCode);
        }

        return json_encode($labels);
    }
}
