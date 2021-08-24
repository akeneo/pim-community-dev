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
SELECT
    association_type.code,
    is_quantified, 
    is_two_way,
    CONCAT('{', GROUP_CONCAT(CONCAT('"', translation.locale, '":"',translation.label,'"')), '}') labels
FROM
    pim_catalog_association_type association_type
LEFT JOIN pim_catalog_association_type_translation translation
          ON association_type.id = translation.foreign_key
WHERE association_type.code IN (:association_type_code)
GROUP BY
    association_type.code;
SQL;

        $rows = $this->connection->executeQuery(
            $query,
            [
                'association_type_code' => $associationTypeCodes,
            ],
            [
                'association_type_code' => Connection::PARAM_STR_ARRAY
            ]
        )->fetchAll();

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
