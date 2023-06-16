<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation\ConsolidateProductScores;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Event\ProductsEvaluated;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EvaluateProducts
{
    public function __construct(
        private EvaluatePendingCriteria $evaluatePendingProductCriteria,
        private EvaluateCriteria $evaluateCriteria,
        private ConsolidateProductScores $consolidateProductScores,
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Pending criteria are fetched from the database (legacy). New way to evaluate products is by events.
     * Use forCriteria instead.
     */
    public function forPendingCriteria(ProductUuidCollection $productUuidCollection): void
    {
        $startTime = time();
        $this->logger->debug('Start product evaluate criteria...');
        $this->evaluatePendingProductCriteria->evaluateAllCriteria($productUuidCollection);
        $afterEvaluationTime = time();
        $this->logger->debug('Start product consolidate...');
        $this->consolidateProductScores->consolidate($productUuidCollection);
        $afterConsolidationTime = time();
        $this->logger->debug('Start product dispatch...');
        $this->eventDispatcher->dispatch(new ProductsEvaluated($productUuidCollection));
        $afterDispatchTime = time();

        $this->logger->info('Finish evaluation products', [
            'evaluation_time_in_sec' => $afterEvaluationTime - $startTime,
            'consolidation_time_in_sec' => $afterConsolidationTime - $afterEvaluationTime,
            'dispatch_time_in_sec' => $afterDispatchTime - $afterConsolidationTime,
        ]);
    }

    /**
     * @param CriterionCode[] $productCriterionCodes
     */
    public function forCriteria(ProductUuidCollection $productUuidCollection, array $productCriterionCodes): void
    {
        $this->evaluateCriteria->forEntityIds($productUuidCollection, $productCriterionCodes);
        $this->consolidateProductScores->consolidate($productUuidCollection);
        $this->eventDispatcher->dispatch(new ProductsEvaluated($productUuidCollection));
    }
}
