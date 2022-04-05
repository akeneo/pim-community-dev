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

use Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation\ConsolidateProductModelScores;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluatePendingCriteria;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\MarkCriteriaToEvaluateInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductIdsToEvaluateQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\BulkUpdateProductQualityScoresInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\UpdateProductsIndex;

final class ProductModelEvaluationInstaller implements FixtureInstaller
{
    private const BATCH_SIZE = 100;
    private const LIMIT = 10000;

    public function __construct(
        private MarkCriteriaToEvaluateInterface         $markProductModelCriteriaToEvaluate,
        private EvaluatePendingCriteria                 $evaluatePendingProductModelCriteria,
        private ConsolidateProductModelScores           $consolidateProductScores,
        private BulkUpdateProductQualityScoresInterface $bulkUpdateProductModelQualityScores,
        private GetProductIdsToEvaluateQueryInterface   $getProductModelIdsToEvaluateQuery
    ) {
    }

    public function install(): void
    {
        $this->markProductModelCriteriaToEvaluate->forUpdatesSince(new \DateTimeImmutable('-1 DAY'), self::BATCH_SIZE);

        foreach ($this->getProductModelIdsToEvaluateQuery->execute(self::LIMIT, self::BATCH_SIZE) as $productIds) {
            $this->evaluatePendingProductModelCriteria->evaluateAllCriteria($productIds);
            $this->consolidateProductScores->consolidate($productIds);
            ($this->bulkUpdateProductModelQualityScores)($productIds);
        }
    }
}
