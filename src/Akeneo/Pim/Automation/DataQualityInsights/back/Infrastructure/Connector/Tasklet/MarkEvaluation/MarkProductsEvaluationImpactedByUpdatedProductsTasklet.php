<?php

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet\MarkEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetUpdatedEntityIdsQueryInterface;
use Psr\Log\LoggerInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MarkProductsEvaluationImpactedByUpdatedProductsTasklet extends AbstractMarkEvaluationTasklet
{
    public function __construct(
        private readonly CreateCriteriaEvaluations $createCriteriaEvaluations,
        private readonly GetUpdatedEntityIdsQueryInterface $getUpdatedProductIdsQuery,
        private readonly LoggerInterface $logger,
        private readonly int $bulkSize
    ) {
    }

    public function execute(): void
    {
        $countMarkedProducts = 0;

        try {
            foreach ($this->getUpdatedProductIdsQuery->since($this->updatedSince(), $this->bulkSize) as $productIdCollection) {
                $this->createCriteriaEvaluations->createAll($productIdCollection);
                $countMarkedProducts += $productIdCollection->count();
            }
        } catch (\Throwable $exception) {
            $this->logger->error(
                'Failed to mark products evaluation impacted by updated products',
                [
                    'error_code' => 'failed_to_mark_product_evaluation',
                    'error_message' => $exception->getMessage(),
                ]
            );
        }

        $this->stepExecution->setWriteCount($countMarkedProducts);
    }
}
