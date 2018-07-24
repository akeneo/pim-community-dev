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

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Repository\EnrichedEntityNotFoundException;
use Akeneo\EnrichedEntity\Domain\Repository\EnrichedEntityRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlEnrichedEntityRepository implements EnrichedEntityRepositoryInterface
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
     * @throws \RuntimeException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function create(EnrichedEntity $enrichedEntity): void
    {
        $serializedLabels = $this->getSerializedLabels($enrichedEntity);
        $insert = <<<SQL
        INSERT INTO akeneo_enriched_entity_enriched_entity (identifier, labels) VALUES (:identifier, :labels);
SQL;
        $affectedRows = $this->sqlConnection->executeUpdate(
            $insert,
            [
                'identifier' => (string) $enrichedEntity->getIdentifier(),
                'labels' => $serializedLabels
            ]
        );
        if ($affectedRows !== 1) {
            throw new \RuntimeException(
                sprintf('Expected to create one enriched entity, but %d were affected', $affectedRows)
            );
        }
    }

    /**
     * @throws \RuntimeException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function update(EnrichedEntity $enrichedEntity): void
    {
        $serializedLabels = $this->getSerializedLabels($enrichedEntity);
        $update = <<<SQL
        UPDATE akeneo_enriched_entity_enriched_entity
        SET labels = :labels
        WHERE identifier = :identifier;
SQL;
        $affectedRows = $this->sqlConnection->executeUpdate(
            $update,
            [
                'identifier' => (string) $enrichedEntity->getIdentifier(),
                'labels' => $serializedLabels
            ]
        );

        if ($affectedRows !== 1) {
            throw new \RuntimeException(
                sprintf('Expected to update one enriched entity, but %d rows were affected.', $affectedRows)
            );
        }
    }

    public function getByIdentifier(EnrichedEntityIdentifier $identifier): EnrichedEntity
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
        $statement->closeCursor();

        if (!$result) {
            throw EnrichedEntityNotFoundException::withIdentifier($identifier);
        }

        return $this->hydrateEnrichedEntity($result['identifier'], $result['labels']);
    }

    public function all(): array
    {
        $selectAllQuery = <<<SQL
        SELECT identifier, labels
        FROM akeneo_enriched_entity_enriched_entity;
SQL;
        $statement = $this->sqlConnection->executeQuery($selectAllQuery);
        $results = $statement->fetchAll();
        $statement->closeCursor();

        $enrichedEntities = [];
        foreach ($results as $result) {
            $enrichedEntities[] = $this->hydrateEnrichedEntity($result['identifier'], $result['labels']);
        }

        return $enrichedEntities;
    }

    private function hydrateEnrichedEntity(string $identifier, string $normalizedLabels): EnrichedEntity
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

    private function getSerializedLabels(EnrichedEntity $enrichedEntity): string
    {
        $labels = [];
        foreach ($enrichedEntity->getLabelCodes() as $localeCode) {
            $labels[$localeCode] = $enrichedEntity->getLabel($localeCode);
        }

        return json_encode($labels);
    }
}
