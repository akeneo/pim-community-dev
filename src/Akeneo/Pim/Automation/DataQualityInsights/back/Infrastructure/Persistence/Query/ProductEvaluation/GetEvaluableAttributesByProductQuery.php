<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Attribute;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetEvaluableAttributesByProductQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Structure\EditableAttributeFilter;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetEvaluableAttributesByProductQuery implements GetEvaluableAttributesByProductQueryInterface
{
    /** * @var Connection */
    private $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function execute(ProductEntityIdInterface $productUuid): array
    {
        Assert::isInstanceOf($productUuid, ProductUuid::class);

        $query = <<<SQL
SELECT
    attribute.code,
    attribute.attribute_type AS type,
    properties,
    attribute.is_localizable
FROM pim_catalog_attribute AS attribute
INNER JOIN pim_catalog_family_attribute AS pca ON attribute.id = pca.attribute_id
INNER JOIN pim_catalog_product AS product ON product.family_id = pca.family_id
INNER JOIN pim_catalog_family AS family ON (product.family_id = family.id)
LEFT JOIN pim_catalog_attribute_group AS attribute_group ON attribute_group.id = attribute.group_id
LEFT JOIN pim_data_quality_insights_attribute_group_activation AS activation ON activation.attribute_group_code = attribute_group.code
WHERE product.uuid = :product_uuid
AND attribute.attribute_type IN (:attribute_types)
AND (activation.activated IS NULL OR activation.activated = 1);
SQL;

        $statement = $this->dbConnection->executeQuery(
            $query,
            [
                'product_uuid' => $productUuid->toBytes(),
                'attribute_types' => AttributeType::EVALUABLE_ATTRIBUTE_TYPES,
            ],
            [
                'product_uuid' => \PDO::PARAM_STR,
                'attribute_types' => Connection::PARAM_STR_ARRAY,
            ]
        );

        $attributes = [];
        foreach (new EditableAttributeFilter($statement->fetchAllAssociative()) as $attribute) {
            $attributes[] = new Attribute(
                new AttributeCode($attribute['code']),
                new AttributeType($attribute['type']),
                (bool) $attribute['is_localizable']
            );
        }

        return $attributes;
    }
}
