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

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\ReferenceEntity;

use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityCollectionType;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityType;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityIsLinkedToAtLeastOneProductAttributeInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class SqlReferenceEntityIsLinkedToAtLeastOneProductAttribute implements ReferenceEntityIsLinkedToAtLeastOneProductAttributeInterface
{
    public function __construct(
        private Connection $sqlConnection
    ) {
    }

    public function isLinked(ReferenceEntityIdentifier $identifier): bool
    {
        return $this->isReferenceEntityLinkedToAtLeastOneProductAttribute($identifier);
    }

    private function fetchResults(): array
    {
        $query = <<<SQL
        SELECT properties
        FROM pim_catalog_attribute
        WHERE attribute_type IN (:attribute_types)
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $query,
            [
                'attribute_types' => [
                    ReferenceEntityCollectionType::REFERENCE_ENTITY_COLLECTION,
                    ReferenceEntityType::REFERENCE_ENTITY,
                ]
            ],
            [
                'attribute_types' => Connection::PARAM_STR_ARRAY
            ]
        );

        $results = $statement->fetchAllAssociative();
        $statement->free();

        return $results;
    }

    private function isReferenceEntityLinkedToAtLeastOneProductAttribute(ReferenceEntityIdentifier $identifier): bool
    {
        $platform = $this->sqlConnection->getDatabasePlatform();
        $results = $this->fetchResults();
        $linkedEntities = [];

        foreach ($results as $result) {
            $properties = Type::getType(Types::ARRAY)->convertToPhpValue($result['properties'], $platform);
            $linkedEntities[] = $properties['reference_data_name'];
        }

        return in_array((string) $identifier, array_unique($linkedEntities));
    }
}
