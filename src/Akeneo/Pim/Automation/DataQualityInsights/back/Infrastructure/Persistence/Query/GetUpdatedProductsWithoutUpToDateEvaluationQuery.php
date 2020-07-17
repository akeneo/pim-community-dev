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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetUpdatedProductsWithoutUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetUpdatedProductIdsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Doctrine\DBAL\Connection;

final class GetUpdatedProductsWithoutUpToDateEvaluationQuery implements GetUpdatedProductsWithoutUpToDateEvaluationQueryInterface
{
    private const GET_UPDATED_PRODUCTS_BULK_SIZE = 1000;

    /** @var Connection */
    private $dbConnection;

    /** @var GetUpdatedProductIdsQueryInterface */
    private $getUpdatedProductIdsQuery;

    public function __construct(Connection $dbConnection, GetUpdatedProductIdsQueryInterface $getUpdatedProductIdsQuery)
    {
        $this->dbConnection = $dbConnection;
        $this->getUpdatedProductIdsQuery = $getUpdatedProductIdsQuery;
    }

    public function execute(\DateTimeImmutable $updatedSince, int $bulkSize): \Iterator
    {
        $productIdsBulk = [];
        foreach ($this->getUpdatedProductIdsQuery->since($updatedSince, self::GET_UPDATED_PRODUCTS_BULK_SIZE) as $productIds) {
            $productIds = $this->filterUpdatedProductIdsWithoutUpToDateEvaluation($updatedSince, $productIds);

            while (!empty($productIds)) {
                $nbProductIdsToPick = max(0, $bulkSize - count($productIdsBulk));
                $productIdsBulk = array_merge($productIdsBulk, array_splice($productIds, 0, $nbProductIdsToPick));

                if (count($productIdsBulk) >= $bulkSize) {
                    yield $this->formatProductIds($productIdsBulk);

                    $productIdsBulk = $nbProductIdsToPick < $bulkSize ? array_splice($productIds, $nbProductIdsToPick) : [];
                }
            }
        }

        if (!empty($productIdsBulk)) {
            yield $this->formatProductIds($productIdsBulk);
        }
    }

    private function filterUpdatedProductIdsWithoutUpToDateEvaluation(\DateTimeImmutable $updatedSince, array $productIds): array
    {
        $sql = <<<SQL
SELECT DISTINCT product.id
FROM pim_catalog_product product
LEFT JOIN pimee_data_quality_insights_criteria_evaluation evaluation
    ON evaluation.product_id = product.id AND evaluation.created_at >= product.updated
WHERE product.id IN (:productIds)
    AND product.product_model_id IS NULL
    AND evaluation.id IS NULL
SQL;

        $stmt = $this->dbConnection->executeQuery(
            $sql,
            ['productIds' => $productIds],
            ['productIds' => Connection::PARAM_INT_ARRAY]
        );

        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    private function formatProductIds(array $productIds): array
    {
        return array_map(function ($productId) {
            return new ProductId(intval($productId));
        }, $productIds);
    }
}
