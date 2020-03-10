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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetUpdatedProductsWithoutUpToDateEvaluationQueryInterface;
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
FROM pim_catalog_product product
LEFT JOIN pimee_data_quality_insights_criteria_evaluation evaluation
    ON evaluation.product_id = product.id AND evaluation.created_at >= product.updated
WHERE product.updated > :updated_since
    AND product.product_model_id IS NULL
    AND evaluation.id IS NULL
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
