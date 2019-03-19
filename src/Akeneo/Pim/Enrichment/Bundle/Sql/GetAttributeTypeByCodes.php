<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Sql;

use Doctrine\DBAL\Connection;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAttributeTypeByCodes
{
    /** @var Connection */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string[] $attributeCodes
     *
     * @return array
     */
    public function execute(array $attributeCodes): array
    {
        $sql = <<<SQL
SELECT a.code, a.attribute_type
FROM pim_catalog_attribute a
WHERE a.code IN (:codes);
SQL;
        $rows = $this->connection->executeQuery(
            $sql,
            ['codes' => $attributeCodes],
            ['codes' => Connection::PARAM_STR_ARRAY]

        )->fetchAll();

        $attributeTypes = [];
        foreach ($rows as $row) {
            $attributeTypes[$row['code']] = $row['attribute_type'];
        }

        return $attributeTypes;
    }
}
