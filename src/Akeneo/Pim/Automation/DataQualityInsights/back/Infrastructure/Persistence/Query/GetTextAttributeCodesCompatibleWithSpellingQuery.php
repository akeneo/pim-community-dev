<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetTextAttributeCodesCompatibleWithSpellingQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Doctrine\DBAL\Connection;

class GetTextAttributeCodesCompatibleWithSpellingQuery implements GetTextAttributeCodesCompatibleWithSpellingQueryInterface
{
    /**
     * @var Connection
     */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function byProductId(ProductId $productId): array
    {
        $query = <<<SQL
SELECT pca.code, pca.properties
FROM pim_catalog_attribute pca
INNER JOIN pim_catalog_family_attribute pcfamatt on pca.id = pcfamatt.attribute_id
INNER JOIN pim_catalog_family pcf on pcfamatt.family_id = pcf.id
INNER JOIN pim_catalog_product pcp on pcp.family_id = pcfamatt.family_id
WHERE pcp.id = :product_id
AND pca.attribute_type = :attribute_type
AND pca.is_localizable = 1
AND pca.id != pcf.label_attribute_id;
SQL;

        $statement = $this->db->executeQuery($query,
            [
                'product_id' => $productId->toInt(),
                'attribute_type' => AttributeTypes::TEXT,
            ],
            [
                'product_id' => \PDO::PARAM_INT,
                'attribute_type' => \PDO::PARAM_STR,
            ]
        );

        $attributes = $this->excludeReadOnlyAttributes($statement->fetchAll());

        return array_map(function (array $attribute) {
            return $attribute['code'];
        }, $attributes);
    }

    private function excludeReadOnlyAttributes(array $attributes): array
    {
        return array_filter($attributes, function ($attribute) {
            if (empty($attribute['properties'])) {
                return true;
            }

            $properties = unserialize($attribute['properties']);
            if (isset($properties['is_read_only']) && $properties['is_read_only'] === true) {
                return false;
            }

            return true;
        });
    }
}
