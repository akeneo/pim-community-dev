<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Query;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\GetTableAttributes;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ReferenceEntityColumn;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

class SqlGetTableAttributes implements GetTableAttributes
{

    public function __construct(
        private Connection $connection,
        private AttributeRepositoryInterface $attributeRepository
    ) {
    }

    function forReferenceEntityIdentifier(string $identifier): array
    {
        $query = <<<SQL
            SELECT * 
                FROM pim_catalog_table_column 
                WHERE properties->"$.reference_entity_identifier" = :identifier
                AND data_type = :referenceEntityColumnDataType
            SQL;

        $result = $this->connection->executeQuery(
            $query,
            [
                'identifier' => $identifier,
                'referenceEntityColumnDataType' => ReferenceEntityColumn::DATATYPE
            ]
        )->fetchAllAssociative();

        $platform = $this->connection->getDatabasePlatform();
        $columns = [];
        foreach ($result as $row) {
            $data = [
                'id' => $row['id'],
                'code' => $row['code'],
                'data_type' => $row['data_type'],
                'labels' => \json_decode($row['labels'], true),
                'validations' => \json_decode($row['validations'], true),
                'is_required_for_completeness' => Type::getType(Types::BOOLEAN)->convertToPhpValue($row['is_required_for_completeness'], $platform),
            ];

            $properties = \json_decode($row['properties'], true);
            if (\array_key_exists('reference_entity_identifier', $properties)) {
                $data['reference_entity_identifier'] = $properties['reference_entity_identifier'];
            }

            $columns[] = ReferenceEntityColumn::fromNormalized($data);

            $this->attributeRepository->findOneByIdentifier($row['attribute_id']); // todo use it or not ? (using SQL id seems a bad idea)
        }

        return $columns;
    }
}
