<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Sql;

use Doctrine\DBAL\Connection;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetVariantAttributeSetAttributeCodes
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
     * @param string $familyVariantCode
     * @param int $level
     *
     * @return string[]
     */
    public function execute(string $familyVariantCode, int $level): array
    {
        $sql = <<<SQL
SELECT pca.code
FROM pim_catalog_family_variant fv
JOIN pim_catalog_family_variant_has_variant_attribute_sets pcfvhvas on pcfvhvas.family_variant_id = fv.id
JOIN pim_catalog_family_variant_attribute_set pcfvas on pcfvhvas.variant_attribute_sets_id = pcfvas.id
JOIN pim_catalog_variant_attribute_set_has_attributes pcvasha on pcfvas.id = pcvasha.variant_attribute_set_id
JOIN pim_catalog_attribute pca on pcvasha.attributes_id = pca.id 
WHERE fv.code = :familyVariantCode
AND pcfvas.level = :level;
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            [
                'familyVariantCode' => $familyVariantCode,
                'level' => $level,
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
