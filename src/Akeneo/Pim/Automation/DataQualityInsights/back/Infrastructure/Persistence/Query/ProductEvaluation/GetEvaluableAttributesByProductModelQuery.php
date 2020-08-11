<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Attribute;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetEvaluableAttributesByProductQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Structure\EditableAttributeFilter;
use Doctrine\DBAL\Connection;

class GetEvaluableAttributesByProductModelQuery implements GetEvaluableAttributesByProductQueryInterface
{
    /** * @var Connection */
    private $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function execute(ProductId $productId): array
    {
        $query = <<<SQL
SELECT
    attribute.code,
    attribute.attribute_type AS type,
    properties,
    attribute.is_localizable
FROM pim_catalog_product_model AS product_model
    INNER JOIN pim_catalog_family_variant AS family_variant ON family_variant.id = product_model.family_variant_id
    INNER JOIN pim_catalog_family AS family ON family.id = family_variant.family_id
    INNER JOIN pim_catalog_family_attribute AS pca ON pca.family_id = family.id
    INNER JOIN pim_catalog_attribute AS attribute ON attribute.id = pca.attribute_id
WHERE product_model.id = :product_model_id
    AND attribute.attribute_type IN (:attribute_types)
    AND NOT EXISTS(
        SELECT 1
        FROM pim_catalog_variant_attribute_set_has_attributes AS attribute_set_attributes
        INNER JOIN pim_catalog_family_variant_attribute_set AS attribute_set ON attribute_set.id = attribute_set_attributes.variant_attribute_set_id
        INNER JOIN pim_catalog_family_variant_has_variant_attribute_sets AS family_attribute_set ON family_attribute_set.variant_attribute_sets_id = attribute_set.id
        WHERE attribute_set_attributes.attributes_id = attribute.id
          AND family_attribute_set.family_variant_id = family_variant.id
          AND (product_model.parent_id IS NULL OR attribute_set.level = 2)
    );
SQL;

        $statement = $this->dbConnection->executeQuery($query,
            [
                'product_model_id' => $productId->toInt(),
                'attribute_types' => AttributeType::EVALUABLE_ATTRIBUTE_TYPES,
            ],
            [
                'product_model_id' => \PDO::PARAM_INT,
                'attribute_types' => Connection::PARAM_STR_ARRAY,
            ]
        );


        $attributes = [];
        foreach (new EditableAttributeFilter($statement->fetchAll()) as $attribute) {
            $attributes[] = new Attribute(
                new AttributeCode($attribute['code']),
                new AttributeType($attribute['type']),
                (bool) $attribute['is_localizable']
            );
        }

        return $attributes;
    }
}
