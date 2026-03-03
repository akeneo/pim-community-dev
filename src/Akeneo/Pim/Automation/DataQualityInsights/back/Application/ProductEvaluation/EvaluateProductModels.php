<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation\ConsolidateProductModelScores;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Event\ProductModelsEvaluated;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EvaluateProductModels
{
    public function __construct(
        private EvaluatePendingCriteria $evaluatePendingProductModelCriteria,
        private ConsolidateProductModelScores $consolidateProductModelScores,
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(ProductModelIdCollection $productModelIdCollection): void
    {
        $startTime = time();
        $this->logger->debug('Start product model evaluate criteria...');
        $this->evaluatePendingProductModelCriteria->evaluateAllCriteria($productModelIdCollection);
        $afterEvaluationTime = time();
        $this->logger->debug('Start product model consolidate...');
        $this->consolidateProductModelScores->consolidate($productModelIdCollection);
        $afterConsolidationTime = time();
        $this->logger->debug('Start product model dispatch...');
        $this->eventDispatcher->dispatch(new ProductModelsEvaluated($productModelIdCollection));
        $afterDispatchTime = time();

        $this->logger->info('Finish evaluation product models', [
            'evaluation_time_in_sec' => $afterEvaluationTime - $startTime,
            'consolidation_time_in_sec' => $afterConsolidationTime - $afterEvaluationTime,
            'dispatch_time_in_sec' => $afterDispatchTime - $afterConsolidationTime,
        ]);
    }
}
