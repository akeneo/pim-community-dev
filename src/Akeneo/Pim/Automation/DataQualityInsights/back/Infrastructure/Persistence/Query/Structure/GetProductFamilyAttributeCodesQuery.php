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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Doctrine\DBAL\Connection;

class GetProductFamilyAttributeCodesQuery implements GetProductFamilyAttributeCodesQueryInterface
{
    private $connection;

    /** @param Connection $connection */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(ProductId $productId): array
    {
        $query = <<<SQL
SELECT attribute.code as attribute_code
FROM pim_catalog_product AS product
INNER JOIN pim_catalog_family AS family ON family.id = product.family_id
INNER JOIN pim_catalog_family_attribute AS family_attribute ON family.id = family_attribute.family_id
INNER JOIN pim_catalog_attribute AS attribute ON family_attribute.attribute_id = attribute.id
LEFT JOIN pim_catalog_attribute_group AS attribute_group ON attribute_group.id = attribute.group_id
LEFT JOIN pim_data_quality_insights_attribute_group_activation AS attribute_group_activation ON attribute_group_activation.attribute_group_code = attribute_group.code
WHERE product.id = :product_id
    AND (attribute_group_activation.activated IS NULL OR attribute_group_activation.activated = 1)
SQL;

        $statement = $this->connection->executeQuery(
            $query,
            ['product_id' => $productId->toInt()],
            ['product_id' => \PDO::PARAM_INT]
        );
        $results = $statement->fetchAll();

        return array_map(
            function ($results) {
                return new AttributeCode($results['attribute_code']);
            },
            $results
        );
    }
}
