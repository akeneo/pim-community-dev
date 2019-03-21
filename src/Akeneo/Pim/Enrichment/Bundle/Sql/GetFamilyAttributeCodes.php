<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Sql;

use Doctrine\DBAL\Connection;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetFamilyAttributeCodes
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
     * @param string $familyCode
     *
     * @return string[]
     */
    public function execute(string $familyCode): array
    {
        $sql = <<<SQL
SELECT a.code
FROM akeneo_pim.pim_catalog_attribute a
JOIN pim_catalog_family_attribute pcfa on pcfa.attribute_id = a.id
JOIN pim_catalog_family f on f.id = pcfa.family_id
WHERE f.code = :family;            
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            [
                'family' => $familyCode,
            ]
        )->fetchAll();

        return array_map(
            function (array $row): string {
                return $row['code'];
            },
            $rows
        );
    }
}
