<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\AttributeGroup;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class GetAttributeGroupCodesAndLabels
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return array<array{code: string, label: string}>
     */
    public function execute(string $locale, string $search, int $offset, int $limit): array
    {
        $query = <<<SQL
SELECT a_group.code as code, a_group_label.label as label
FROM pim_catalog_attribute_group as a_group
LEFT JOIN pim_catalog_attribute_group_translation as a_group_label
    ON a_group.id = a_group_label.foreign_key AND a_group_label.locale = :locale
WHERE COALESCE(a_group_label.label, a_group.code) LIKE :search
ORDER BY COALESCE(a_group_label.label, a_group.code)
LIMIT :limit OFFSET :offset;
SQL;

        return $this->connection->fetchAllAssociative(
            $query,
            [
                'search' => '%'.$search.'%',
                'locale' => $locale,
                'limit' => $limit,
                'offset' => $offset,
            ],
            [
                'offset' => ParameterType::INTEGER,
                'limit' => ParameterType::INTEGER,
            ]
        );
    }
}
