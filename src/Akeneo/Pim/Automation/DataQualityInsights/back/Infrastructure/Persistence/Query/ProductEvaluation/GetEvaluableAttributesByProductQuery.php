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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Attribute;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetEvaluableAttributesByProductQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Structure\EditableAttributeFilter;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Structure\MapAttributeType;
use Doctrine\DBAL\Connection;

class GetEvaluableAttributesByProductQuery implements GetEvaluableAttributesByProductQueryInterface
{
    /** * @var Connection */
    private $dbConnection;

    /** @var MapAttributeType */
    private $attributeTypeMapper;

    public function __construct(Connection $dbConnection, MapAttributeType $attributeTypeMapper)
    {
        $this->dbConnection = $dbConnection;
        $this->attributeTypeMapper = $attributeTypeMapper;
    }

    public function execute(ProductId $productId): array
    {
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
WHERE product.id = :product_id
AND attribute.attribute_type IN (:attribute_types);
SQL;

        $evaluableAttributeTypes = $this->attributeTypeMapper->fromArrayStringToPimStructure(AttributeType::EVALUABLE_ATTRIBUTE_TYPES);

        $statement = $this->dbConnection->executeQuery($query,
            [
                'product_id' => $productId->toInt(),
                'attribute_types' => $evaluableAttributeTypes,
            ],
            [
                'product_id' => \PDO::PARAM_INT,
                'attribute_types' => Connection::PARAM_STR_ARRAY,
            ]
        );

        $attributes = [];
        foreach (new EditableAttributeFilter($statement->fetchAll()) as $attribute) {
            $attributes[] = new Attribute(
                new AttributeCode($attribute['code']),
                $this->attributeTypeMapper->fromPimStructure($attribute['type']),
                (bool) $attribute['is_localizable'],
                false
            );
        }

        return $attributes;
    }
}
