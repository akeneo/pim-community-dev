<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\Association\Sql;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\AssociationType;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\GetAssociationTypesInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\LabelCollection;
use Doctrine\DBAL\Connection;

final class SqlGetAssociationTypes implements GetAssociationTypesInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @inheritDoc
     */
    public function forCodes(array $associationTypeCodes): array
    {
        $query = <<<SQL
WITH association_type_labels AS (
    SELECT foreign_key as association_type_id, JSON_OBJECTAGG(locale, label) as labels
    FROM pim_catalog_association_type_translation
    GROUP BY foreign_key
)
SELECT
    association_type.code,
    association_type.is_quantified, 
    association_type.is_two_way,
    labels
FROM pim_catalog_association_type association_type
LEFT JOIN association_type_labels ON association_type_id = association_type.id
WHERE association_type.code IN (:association_type_code)
SQL;

        $rows = $this->connection->executeQuery(
            $query,
            [
                'association_type_code' => $associationTypeCodes,
            ],
            [
                'association_type_code' => Connection::PARAM_STR_ARRAY
            ]
        )->fetchAllAssociative();

        $associationTypes = [];
        foreach ($rows as $row) {
            $associationTypes[$row['code']] = new AssociationType(
                $row['code'],
                LabelCollection::fromArray(\json_decode($row['labels'] ?? '{}', true)),
                \boolval($row['is_two_way']),
                \boolval($row['is_quantified'])
            );
        }

        return $associationTypes;
    }
}
