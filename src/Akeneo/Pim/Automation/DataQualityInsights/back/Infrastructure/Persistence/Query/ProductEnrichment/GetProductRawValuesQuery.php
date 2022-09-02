<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEnrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetProductRawValuesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductRawValuesQuery implements GetProductRawValuesQueryInterface
{
    /** * @var Connection */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function execute(ProductEntityIdInterface $productId): array
    {
        Assert::isInstanceOf($productId, ProductUuid::class);

        $query = <<<SQL
SELECT
    JSON_MERGE(
        COALESCE(pm1.raw_values, '{}'),
        COALESCE(pm2.raw_values, '{}'),
        product.raw_values
    ) AS raw_values
FROM pim_catalog_product as product
    LEFT JOIN pim_catalog_product_model pm1 ON product.product_model_id = pm1.id
    LEFT JOIN pim_catalog_product_model pm2 ON pm1.parent_id = pm2.id
WHERE product.uuid = :product_uuid;
SQL;

        $statement = $this->db->executeQuery(
            $query,
            [
                'product_uuid' => $productId->toBytes(),
            ],
            [
                'product_uuid' => \PDO::PARAM_STR,
            ]
        );

        $result = $statement->fetchOne();

        return false === $result ? [] : json_decode($result, true);
    }
}
