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

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetUpdatedProductIdsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetUpdatedProductsWithoutUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Doctrine\DBAL\Connection;

class GetUpdatedProductModelsWithoutUpToDateEvaluationQuery implements GetUpdatedProductsWithoutUpToDateEvaluationQueryInterface
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
        $productModelIdsBulk = [];
        foreach ($this->getUpdatedProductIdsQuery->since($updatedSince, self::GET_UPDATED_PRODUCTS_BULK_SIZE) as $productModelIds) {
            $productModelIds = $this->filterUpdatedProductModelIdsWithoutUpToDateEvaluation($updatedSince, $productModelIds);

            while (!empty($productModelIds)) {
                $nbProductIdsToPick = max(0, $bulkSize - count($productModelIdsBulk));
                $productModelIdsBulk = array_merge($productModelIdsBulk, array_splice($productModelIds, 0, $nbProductIdsToPick));

                if (count($productModelIdsBulk) >= $bulkSize) {
                    yield $this->formatProductIds($productModelIdsBulk);

                    $productModelIdsBulk = $nbProductIdsToPick < $bulkSize ? array_splice($productModelIds, $nbProductIdsToPick) : [];
                }
            }
        }

        if (!empty($productModelIdsBulk)) {
            yield $this->formatProductIds($productModelIdsBulk);
        }
    }

    private function filterUpdatedProductModelIdsWithoutUpToDateEvaluation(\DateTimeImmutable $updatedSince, array $productModelIds): array
    {
        $sql = <<<SQL
    SELECT product_model.id
    FROM pim_catalog_product_model AS product_model
        LEFT JOIN pim_catalog_product_model AS parent ON parent.id = product_model.parent_id
    WHERE product_model.id IN (:productModelIds)
        AND (
            EXISTS(
                SELECT 1 FROM pimee_data_quality_insights_product_model_criteria_evaluation AS evaluation
                WHERE evaluation.product_id = product_model.id
                AND (
                    evaluation.evaluated_at < product_model.updated
                    OR (parent.updated IS NOT NULL AND evaluation.evaluated_at < parent.updated)
                )
                AND evaluation.status != :statusPending
            )
            OR NOT EXISTS(
                SELECT 1 FROM pimee_data_quality_insights_product_model_criteria_evaluation AS missing_evaluation
                WHERE missing_evaluation.product_id = product_model.id
            )
      );
SQL;

        $stmt = $this->dbConnection->executeQuery(
            $sql,
            [
                'productModelIds' => $productModelIds,
                'updatedSince' => $updatedSince->format(Clock::TIME_FORMAT),
                'statusPending' => CriterionEvaluationStatus::PENDING,
            ],
            [
                'productModelIds' => Connection::PARAM_INT_ARRAY,
            ]
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
