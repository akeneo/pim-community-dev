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
use Akeneo\EnrichedEntity\Domain\Query\EnrichedEntity\EnrichedEntityIsLinkedToAtLeastOneProductAttributeInterface;
use Akeneo\Pim\EnrichedEntity\Component\AttributeType\EnrichedEntityCollectionType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use PDO;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class SqlEnrichedEntityIsLinkedToAtLeastOneProductAttribute implements EnrichedEntityIsLinkedToAtLeastOneProductAttributeInterface
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function __invoke(EnrichedEntityIdentifier $identifier): bool
    {
        return $this->isEnrichedEntityLinkedToAtLeastOneProductAttribute($identifier);
    }

    private function fetchResults(): array
    {
        $query = <<<SQL
        SELECT properties
        FROM pim_catalog_attribute
        WHERE attribute_type = :attribute_type;
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'attribute_type' => EnrichedEntityCollectionType::ENRICHED_ENTITY_COLLECTION,
        ]);

        $results = $statement->fetchAll(PDO::FETCH_ASSOC);
        $statement->closeCursor();

        return $results;
    }

    private function isEnrichedEntityLinkedToAtLeastOneProductAttribute(EnrichedEntityIdentifier $identifier): bool
    {
        $platform = $this->sqlConnection->getDatabasePlatform();
        $results = $this->fetchResults();
        $linkedEntities = [];

        foreach ($results as $result) {
            $properties = Type::getType(Type::TARRAY)->convertToPhpValue($result['properties'], $platform);
            $linkedEntities[] = $properties['reference_data_name'];
        }

        return in_array((string) $identifier, array_unique($linkedEntities));
    }
}
