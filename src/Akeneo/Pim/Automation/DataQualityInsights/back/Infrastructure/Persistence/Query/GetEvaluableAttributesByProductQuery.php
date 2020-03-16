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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Attribute;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetEvaluableAttributesByProductQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Doctrine\DBAL\Connection;

class GetEvaluableAttributesByProductQuery implements GetEvaluableAttributesByProductQueryInterface
{
    private const EVALUABLE_ATTRIBUTE_TYPES = [
        AttributeTypes::TEXT,
        AttributeTypes::TEXTAREA,
    ];

    /** @var AttributeType[] */
    private $attributeTypes;

    /** * @var Connection */
    private $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
        $this->attributeTypes = [
            AttributeTypes::TEXT => AttributeType::text(),
            AttributeTypes::TEXTAREA => AttributeType::textarea(),
        ];
    }

    public function execute(ProductId $productId): array
    {
        $query = <<<SQL
SELECT
    attribute.code, 
    attribute.attribute_type AS type, 
    properties, 
    attribute.is_localizable, 
    (family.label_attribute_id = pca.attribute_id) AS is_main_title
FROM pim_catalog_attribute AS attribute
INNER JOIN pim_catalog_family_attribute AS pca ON attribute.id = pca.attribute_id
INNER JOIN pim_catalog_product AS product ON product.family_id = pca.family_id
INNER JOIN pim_catalog_family AS family ON (product.family_id = family.id)
WHERE product.id = :product_id
AND attribute.attribute_type IN (:attribute_types);
SQL;

        $statement = $this->dbConnection->executeQuery($query,
            [
                'product_id' => $productId->toInt(),
                'attribute_types' => self::EVALUABLE_ATTRIBUTE_TYPES,
            ],
            [
                'product_id' => \PDO::PARAM_INT,
                'attribute_types' => Connection::PARAM_STR_ARRAY,
            ]
        );

        $attributes = $this->excludeReadOnlyAttributes($statement->fetchAll());

        return array_map(function (array $attribute) {
            return new Attribute(
                new AttributeCode($attribute['code']),
                $this->getAttributeType($attribute['type']),
                (bool) $attribute['is_localizable'],
                (bool) $attribute['is_main_title']
            );
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

    private function getAttributeType(string $type): AttributeType
    {
        if (!isset($this->attributeTypes[$type])) {
            throw new \RuntimeException(sprintf('Unexpected attribute type "%s"', $type));
        }

        return $this->attributeTypes[$type];
    }
}
