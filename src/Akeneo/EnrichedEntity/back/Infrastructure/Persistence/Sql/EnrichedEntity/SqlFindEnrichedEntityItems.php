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

namespace Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\EnrichedEntity;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Image;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Query\EnrichedEntity\EnrichedEntityItem;
use Akeneo\EnrichedEntity\Domain\Query\EnrichedEntity\FindEnrichedEntityItemsInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * TODO: think about cursor/es index
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindEnrichedEntityItems implements FindEnrichedEntityItemsInterface
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
    public function __invoke(): array
    {
        $results = $this->fetchResults();
        $enrichedEntityItems = [];
        foreach ($results as $result) {
            $enrichedEntityItems[] = $this->hydrateEnrichedEntityItem(
                $result['identifier'],
                $result['labels'],
                $result['image']
            );
        }

        return $enrichedEntityItems;
    }

    private function fetchResults(): array
    {
        $query = <<<SQL
        SELECT identifier, labels, image
        FROM akeneo_enriched_entity_enriched_entity
SQL;
        $statement = $this->sqlConnection->executeQuery($query);
        $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $statement->closeCursor();

        return $results;
    }

    private function hydrateEnrichedEntityItem(
        string $identifier,
        string $normalizedLabels,
        ?string $image = null
    ): EnrichedEntityItem {
        $platform = $this->sqlConnection->getDatabasePlatform();

        $labels = json_decode($normalizedLabels, true);
        $identifier = Type::getType(Type::STRING)->convertToPHPValue($identifier, $platform);

        $enrichedEntityItem = new EnrichedEntityItem();
        $enrichedEntityItem->identifier = EnrichedEntityIdentifier::fromString($identifier);
        $enrichedEntityItem->labels = LabelCollection::fromArray($labels);
        $enrichedEntityItem->image = (null !== $image) ? Image::fromString($image) : null;

        return $enrichedEntityItem;
    }
}
