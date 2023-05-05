<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Messenger;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CriteriaByFeatureRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProductModels;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProducts;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetOutdatedProductModelIdsByDateAndCriteriaQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetOutdatedProductUuidsByDateAndCriteriaQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Psr\Log\LoggerInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LaunchProductAndProductModelEvaluationsHandler
{
    public function __construct(
        private readonly CriteriaByFeatureRegistry $productCriteriaRegistry,
        private readonly CriteriaByFeatureRegistry $productModelCriteriaRegistry,
        private readonly CreateCriteriaEvaluations $createProductCriteriaEvaluations,
        private readonly CreateCriteriaEvaluations $createProductModelCriteriaEvaluations,
        private readonly EvaluateProducts $evaluateProducts,
        private readonly EvaluateProductModels $evaluateProductModels,
        private readonly GetOutdatedProductUuidsByDateAndCriteriaQueryInterface $getOutdatedProductUuids,
        private readonly GetOutdatedProductModelIdsByDateAndCriteriaQueryInterface $getOutdatedProductModelIds,
        private readonly LoggerInterface $logger
    ) {
    }

    public function __invoke(LaunchProductAndProductModelEvaluationsMessage $message): void
    {
        $this->logger->debug('Handler ' . get_class($this) . ' received a message: LaunchProductAndProductModelEvaluationsMessage');

        if (!$message->productUuids->isEmpty()) {
            $this->evaluateProducts($message);
        }

        if (!$message->productModelIds->isEmpty()) {
            $this->evaluateProductModels($message);
        }
    }

    private function evaluateProducts(LaunchProductAndProductModelEvaluationsMessage $message): void
    {
        $productUuidsToEvaluate = ($this->getOutdatedProductUuids)($message->productUuids, $message->datetime, $message->criteriaToEvaluate);

        if ($productUuidsToEvaluate->isEmpty()) {
            $this->logger->debug('DQI - All products have already been evaluated');
            return;
        }

        $criteriaToEvaluate = empty($message->criteriaToEvaluate)
            ? $this->productCriteriaRegistry->getAllCriterionCodes()
            : \array_map(fn (string $criterionCode) => new CriterionCode($criterionCode), $message->criteriaToEvaluate);

        $this->createProductCriteriaEvaluations->create($criteriaToEvaluate, $productUuidsToEvaluate);
        ($this->evaluateProducts)($productUuidsToEvaluate);

        $this->logger->debug(sprintf('DQI - Evaluation of %d products done', $productUuidsToEvaluate->count()));
    }

    private function evaluateProductModels(LaunchProductAndProductModelEvaluationsMessage $message): void
    {
        $productModelIdsToEvaluate = ($this->getOutdatedProductModelIds)($message->productModelIds, $message->datetime, $message->criteriaToEvaluate);

        if ($productModelIdsToEvaluate->isEmpty()) {
            $this->logger->debug('DQI - All product-models have already been evaluated');
            return;
        }

        $criteriaToEvaluate = empty($message->criteriaToEvaluate)
            ? $this->productModelCriteriaRegistry->getAllCriterionCodes()
            : \array_map(fn (string $criterionCode) => new CriterionCode($criterionCode), $message->criteriaToEvaluate);

        $this->createProductModelCriteriaEvaluations->create($criteriaToEvaluate, $productModelIdsToEvaluate);
        ($this->evaluateProductModels)($productModelIdsToEvaluate);

        $this->logger->debug(sprintf('DQI - Evaluation of %d product-models done', $productModelIdsToEvaluate->count()));
    }
}
