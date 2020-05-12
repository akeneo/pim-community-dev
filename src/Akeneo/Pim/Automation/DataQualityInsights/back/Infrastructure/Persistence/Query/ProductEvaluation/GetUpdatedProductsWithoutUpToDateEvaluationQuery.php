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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetUpdatedProductsWithoutUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Doctrine\DBAL\Connection;

final class GetUpdatedProductsWithoutUpToDateEvaluationQuery implements GetUpdatedProductsWithoutUpToDateEvaluationQueryInterface
{
    /** @var Connection */
    private $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function execute(\DateTimeImmutable $updatedSince, int $bulkSize): \Iterator
    {
        $sql = <<<SQL
    SELECT DISTINCT product.id
    FROM pim_catalog_product AS product
    LEFT JOIN pimee_data_quality_insights_product_criteria_evaluation AS evaluation
        ON evaluation.product_id = product.id AND evaluation.evaluated_at >= product.updated
    WHERE product.updated > :updated_since AND evaluation.product_id IS NULL
UNION
    SELECT DISTINCT product.id
    FROM pim_catalog_product_model AS parent
    INNER JOIN pim_catalog_product AS product
        ON product.product_model_id = parent.id AND product.updated < parent.updated
    LEFT JOIN pimee_data_quality_insights_product_criteria_evaluation AS evaluation
        ON evaluation.product_id = product.id AND evaluation.evaluated_at >= parent.updated
    WHERE parent.updated > :updated_since AND evaluation.product_id IS NULL
UNION
    SELECT DISTINCT product.id
    FROM pim_catalog_product_model AS grand_parent
    INNER JOIN pim_catalog_product_model AS parent ON parent.parent_id = grand_parent.id
    INNER JOIN pim_catalog_product AS product
        ON product.product_model_id = parent.id AND product.updated < grand_parent.updated
    LEFT JOIN pimee_data_quality_insights_product_criteria_evaluation AS evaluation
        ON evaluation.product_id = product.id AND evaluation.evaluated_at >= grand_parent.updated
    WHERE grand_parent.updated > :updated_since  AND evaluation.product_id IS NULL
SQL;

        $stmt = $this->dbConnection->executeQuery(
            $sql,
            ['updated_since' => $updatedSince->format('Y-m-d H:i:s')],
            ['updated_since' => \PDO::PARAM_STR]
        );

        $productIds = [];
        while ($productId = $stmt->fetchColumn()) {
            $productIds[] = new ProductId(intval($productId));

            if (count($productIds) >= $bulkSize) {
                yield $productIds;
                $productIds = [];
            }
        }

        if (!empty($productIds)) {
            yield $productIds;
        }
    }
}
