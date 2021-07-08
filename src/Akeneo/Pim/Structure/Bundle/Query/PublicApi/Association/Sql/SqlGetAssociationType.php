<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\Association\Sql;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\AssociationType;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\GetAssociationTypeInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\LabelCollection;
use Doctrine\DBAL\Connection;

final class SqlGetAssociationType implements GetAssociationTypeInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(string $associationTypeCode): ?AssociationType
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
WHERE association_type.code = :association_type_code
GROUP BY
    association_type.code;
SQL;

        $rawResult = $this->connection->executeQuery(
            $query,
            [
                'association_type_code' => $associationTypeCode,
            ],
            [
                'association_type_code' => \PDO::PARAM_STR,
            ]
        )->fetch();

        if (!$rawResult) {
            return null;
        }

        return new AssociationType(
            $rawResult['code'],
            LabelCollection::fromArray(\json_decode($rawResult['labels'], true)),
            \boolval($rawResult['is_quantified']),
            \boolval($rawResult['is_two_way'])
        );
    }
}
