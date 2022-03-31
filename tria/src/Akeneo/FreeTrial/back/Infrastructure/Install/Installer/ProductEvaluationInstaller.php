<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\FreeTrial\Infrastructure\Install\Installer;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation\ConsolidateProductScores;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluatePendingCriteria;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\MarkCriteriaToEvaluateInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductIdsToEvaluateQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\BulkUpdateProductQualityScoresInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\UpdateProductsIndex;

final class ProductEvaluationInstaller implements FixtureInstaller
{
    private const BATCH_SIZE = 100;

    private const LIMIT = 10000;

    private MarkCriteriaToEvaluateInterface $markProductCriteriaToEvaluate;

    private EvaluatePendingCriteria $evaluatePendingProductCriteria;

    private ConsolidateProductScores $consolidateProductScores;

    private BulkUpdateProductQualityScoresInterface $bulkUpdateProductQualityScores;

    private GetProductIdsToEvaluateQueryInterface $getProductIdsToEvaluateQuery;

    public function __construct(
        MarkCriteriaToEvaluateInterface $markProductCriteriaToEvaluate,
        EvaluatePendingCriteria $evaluatePendingProductCriteria,
        ConsolidateProductScores $consolidateProductScores,
        BulkUpdateProductQualityScoresInterface $bulkUpdateProductQualityScores,
        GetProductIdsToEvaluateQueryInterface $getProductIdsToEvaluateQuery
    ) {
        $this->markProductCriteriaToEvaluate = $markProductCriteriaToEvaluate;
        $this->evaluatePendingProductCriteria = $evaluatePendingProductCriteria;
        $this->consolidateProductScores = $consolidateProductScores;
        $this->bulkUpdateProductQualityScores = $bulkUpdateProductQualityScores;
        $this->getProductIdsToEvaluateQuery = $getProductIdsToEvaluateQuery;
    }

    public function install(): void
    {
        $this->markProductCriteriaToEvaluate->forUpdatesSince(new \DateTimeImmutable('-1 DAY'), self::BATCH_SIZE);

        foreach ($this->getProductIdsToEvaluateQuery->execute(self::LIMIT, self::BATCH_SIZE) as $productIds) {
            $this->evaluatePendingProductCriteria->evaluateAllCriteria($productIds);
            $this->consolidateProductScores->consolidate($productIds);
            ($this->bulkUpdateProductQualityScores)($productIds);
        }
    }
}
