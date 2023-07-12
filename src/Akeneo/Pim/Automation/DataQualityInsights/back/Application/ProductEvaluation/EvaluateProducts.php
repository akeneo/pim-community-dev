<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation\ConsolidateProductScores;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Event\ProductsEvaluated;
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
        private EvaluatePendingCriteria  $evaluatePendingProductCriteria,
        private ConsolidateProductScores $consolidateProductScores,
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(ProductUuidCollection $productUuidCollection): void
    {
        $startTime = time();
        $this->evaluatePendingProductCriteria->evaluateAllCriteria($productUuidCollection);
        $afterEvaluationTime = time();
        $this->consolidateProductScores->consolidate($productUuidCollection);
        $afterConsolidationTime = time();
        $this->eventDispatcher->dispatch(new ProductsEvaluated($productUuidCollection));
        $afterDispatchTime = time();

        $this->logger->info('Finish evaluation products', [
            'evaluation_time_in_sec' => $afterEvaluationTime - $startTime,
            'consolidation_time_in_sec' => $afterConsolidationTime - $afterEvaluationTime,
            'dispatch_time_in_sec' => $afterDispatchTime - $afterConsolidationTime,
        ]);
    }
}
