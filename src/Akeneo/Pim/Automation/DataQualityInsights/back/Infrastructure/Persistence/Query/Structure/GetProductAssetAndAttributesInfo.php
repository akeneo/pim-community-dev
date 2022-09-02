<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure;

use Doctrine\DBAL\Connection;

class GetProductAssetAndAttributesInfo implements GetProductAssetAndAttributesInfoInterface
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @inheritDoc
     */
    public function forProductFamilyCodes(array $familyCodes): array
    {
        $sql = <<<SQL
SELECT pim_catalog_family.code AS family_code, pim_catalog_attribute.code AS attribute_code, pim_catalog_attribute.properties
FROM pim_catalog_family
JOIN pim_catalog_family_attribute ON pim_catalog_family.id = pim_catalog_family_attribute.family_id
JOIN pim_catalog_attribute ON pim_catalog_attribute.id = pim_catalog_family_attribute.attribute_id
WHERE pim_catalog_family.code IN (:familyCodes)
AND pim_catalog_attribute.attribute_type = 'pim_catalog_asset_collection';
SQL;
        $rows = $this->connection
            ->executeQuery(
                $sql,
                ['familyCodes' => $familyCodes],
                ['familyCodes' => Connection::PARAM_STR_ARRAY]
            )
            ->fetchAllAssociative();

        $assetFamilyIdentifiers = [];
        foreach ($rows as $row) {
            $properties = \unserialize($row['properties']);
            $referenceDataName = $properties['reference_data_name'] ?? null;
            if (null !== $referenceDataName) {
                $assetFamilyIdentifiers[$row['family_code']] [] = [
                    'attribute_code' => $row['attribute_code'],
                    'asset_family_identifier' => $referenceDataName
                ];
            }
        }

        return $assetFamilyIdentifiers;
    }
}
