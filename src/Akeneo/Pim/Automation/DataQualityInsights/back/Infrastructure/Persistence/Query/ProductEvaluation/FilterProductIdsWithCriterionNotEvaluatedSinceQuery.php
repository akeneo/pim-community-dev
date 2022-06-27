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
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\FilterProductIdsWithCriterionNotEvaluatedSinceQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

final class FilterProductIdsWithCriterionNotEvaluatedSinceQuery implements FilterProductIdsWithCriterionNotEvaluatedSinceQueryInterface
{
    public function __construct(
        private Connection                      $dbConnection,
        private string                          $tableName,
        private ProductEntityIdFactoryInterface $idFactory
    ) {
    }

    public function execute(
        ProductEntityIdCollection $productIds,
        \DateTimeImmutable $evaluatedSince,
        CriterionCode $criterionCode
    ): ProductEntityIdCollection {
        if ($productIds instanceof ProductUuidCollection) {
            return $this->executeForProductUuidCollection($productIds, $evaluatedSince, $criterionCode);
        }

        Assert::isInstanceOf($productIds, ProductModelIdCollection::class);

        return $this->executeForProductModelIdCollection($productIds, $evaluatedSince, $criterionCode);
    }

    private function executeForProductUuidCollection(
        ProductUuidCollection $productUuids,
        \DateTimeImmutable $evaluatedSince,
        CriterionCode $criterionCode
    ): ProductEntityIdCollection {
        $tableName = $this->tableName;

        $query = <<<SQL
SELECT BIN_TO_UUID(product.uuid) AS product_uuid
FROM $tableName AS evaluation
    JOIN pim_catalog_product product ON product.uuid = evaluation.product_uuid
WHERE product.uuid IN (:productUuids) AND criterion_code = :criterionCode
    AND status != 'pending' AND (evaluated_at IS NULL OR evaluated_at < :evaluateSince)
SQL;

        $stmt = $this->dbConnection->executeQuery($query, [
            'productUuids' => $productUuids->toArrayBytes(),
            'criterionCode' => $criterionCode,
            'evaluateSince' => $evaluatedSince->format(Clock::TIME_FORMAT),
        ], [
            'productUuids' => Connection::PARAM_STR_ARRAY,
        ]);


        return $this->idFactory->createCollection($stmt->fetchFirstColumn());
    }

    private function executeForProductModelIdCollection(
        ProductModelIdCollection $productModelIds,
        \DateTimeImmutable $evaluatedSince,
        CriterionCode $criterionCode
    ): ProductEntityIdCollection {
        $tableName = $this->tableName;

        $query = <<<SQL
SELECT product_id
FROM $tableName AS evaluation
WHERE product_id IN (:productModelIds) AND criterion_code = :criterionCode
    AND status != 'pending' AND (evaluated_at IS NULL OR evaluated_at < :evaluateSince)
SQL;

        $stmt = $this->dbConnection->executeQuery($query, [
            'productModelIds' => array_map(fn ($productModelId) => (int)$productModelId, $productModelIds->toArrayString()),
            'criterionCode' => $criterionCode,
            'evaluateSince' => $evaluatedSince->format(Clock::TIME_FORMAT),
        ], [
            'productModelIds' => Connection::PARAM_INT_ARRAY,
        ]);


        return $this->idFactory->createCollection($stmt->fetchFirstColumn());
    }
}
