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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetProductFamilyAttributeCodesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

class GetProductModelFamilyAttributeCodesQuery implements GetProductFamilyAttributeCodesQueryInterface
{
    private $connection;

    /** @param Connection $connection */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(ProductEntityIdInterface $productId): array
    {
        Assert::isInstanceOf($productId, ProductModelId::class);

        $query = <<<SQL
SELECT attribute.code
FROM pim_catalog_product_model AS product_model
    INNER JOIN pim_catalog_family_variant AS family_variant ON family_variant.id = product_model.family_variant_id
    INNER JOIN pim_catalog_family AS family ON family.id = family_variant.family_id
    INNER JOIN pim_catalog_family_attribute AS pca ON pca.family_id = family.id
    INNER JOIN pim_catalog_attribute AS attribute ON attribute.id = pca.attribute_id
    LEFT JOIN pim_catalog_attribute_group AS attribute_group ON attribute_group.id = attribute.group_id
    LEFT JOIN pim_data_quality_insights_attribute_group_activation AS activation ON activation.attribute_group_code = attribute_group.code
WHERE product_model.id = :product_model_id
    AND (activation.activated IS NULL OR activation.activated = 1)
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

        $statement = $this->connection->executeQuery(
            $query,
            ['product_model_id' => $productId->toInt()],
            ['product_model_id' => \PDO::PARAM_INT]
        );
        $results = $statement->fetchAllAssociative();

        return array_map(
            function ($results) {
                return new AttributeCode($results['code']);
            },
            $results
        );
    }
}
