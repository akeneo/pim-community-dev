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

namespace Akeneo\EnrichedEntity\back\Infrastructure\Persistence\Sql;

use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\back\Domain\Query\EnrichedEntityDetails;
use Akeneo\EnrichedEntity\back\Domain\Query\FindEnrichedEntityDetailsInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindEnrichedEntityDetails implements FindEnrichedEntityDetailsInterface
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
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __invoke(EnrichedEntityIdentifier $identifier): ?EnrichedEntityDetails
    {
        $result = $this->fetchResult($identifier);

        if (empty($result)) {
            return null;
        }

        return $this->hydrateEnrichedEntityDetails($result['identifier'], $result['labels']);
    }

    private function fetchResult(EnrichedEntityIdentifier $identifier): array
    {
        $query = <<<SQL
        SELECT identifier, labels
        FROM akeneo_enriched_entity_enriched_entity
        WHERE identifier = :identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'identifier' => (string)$identifier,
        ]);

        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        $statement->closeCursor();

        return !$result ? [] : $result;
    }

    /**
     * @param string $identifier
     * @param string $normalizedLabels
     *
     * @return EnrichedEntityDetails
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function hydrateEnrichedEntityDetails(
        string $identifier,
        string $normalizedLabels
    ): EnrichedEntityDetails {
        $platform = $this->sqlConnection->getDatabasePlatform();

        $labels = json_decode($normalizedLabels, true);
        $identifier = Type::getType(Type::STRING)->convertToPHPValue($identifier, $platform);

        $enrichedEntityItem = new EnrichedEntityDetails();
        $enrichedEntityItem->identifier = EnrichedEntityIdentifier::fromString($identifier);
        $enrichedEntityItem->labels = LabelCollection::fromArray($labels);

        return $enrichedEntityItem;
    }
}
