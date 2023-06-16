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
        private readonly EvaluateProducts $evaluateProducts,
        private readonly EvaluateProductModels $evaluateProductModels,
        private readonly GetOutdatedProductUuidsByDateAndCriteriaQueryInterface $getOutdatedProductUuids,
        private readonly GetOutdatedProductModelIdsByDateAndCriteriaQueryInterface $getOutdatedProductModelIds,
        private readonly LoggerInterface $logger
    ) {
    }

    public function __invoke(LaunchProductAndProductModelEvaluationsMessage $message): void
    {
        $startTime = time();
        if (!$message->productUuids->isEmpty()) {
            $this->evaluateProducts($message);
        }

        if (!$message->productModelIds->isEmpty()) {
            $this->evaluateProductModels($message);
        }

        $this->logger->notice('LaunchProductAndProductModelEvaluationsMessage is handled', [
            'duration_time_in_secs' => time() - $startTime,
            'count_products' => $message->productUuids->count(),
            'count_product_models' => $message->productModelIds->count(),
        ]);
    }

    private function evaluateProducts(LaunchProductAndProductModelEvaluationsMessage $message): void
    {
        // @TODO: change that, we can't use this table because we don't use it anymore!
        $productUuidsToEvaluate = ($this->getOutdatedProductUuids)(
            $message->productUuids,
            $message->datetime,
            $message->criteriaToEvaluate
        );
        if ($productUuidsToEvaluate->isEmpty()) {
            $this->logger->debug('DQI - All products have already been evaluated');
            return;
        }

        $this->logger->debug(sprintf('DQI - Evaluation of %d products start', $productUuidsToEvaluate->count()));
        $criteriaToEvaluate = \array_map(
            static fn (string $criterionCode): CriterionCode => new CriterionCode($criterionCode),
            $message->criteriaToEvaluate
        );
        $this->evaluateProducts->forCriteria($productUuidsToEvaluate, $criteriaToEvaluate);

        $this->logger->debug(sprintf('DQI - Evaluation of %d products done', $productUuidsToEvaluate->count()));
    }

    private function evaluateProductModels(LaunchProductAndProductModelEvaluationsMessage $message): void
    {
        // @TODO: change that, we can't use this table because we don't use it anymore!
        $productModelIdsToEvaluate = ($this->getOutdatedProductModelIds)(
            $message->productModelIds,
            $message->datetime,
            $message->criteriaToEvaluate
        );

        if ($productModelIdsToEvaluate->isEmpty()) {
            $this->logger->debug('DQI - All product-models have already been evaluated');
            return;
        }

        $this->logger->debug(sprintf('DQI - Evaluation of %d product-models start', $productModelIdsToEvaluate->count()));
        $criteriaToEvaluate = \array_map(
            static fn (string $criterionCode): CriterionCode => new CriterionCode($criterionCode),
            $message->criteriaToEvaluate
        );
        $this->evaluateProductModels->forCriteria($productModelIdsToEvaluate, $criteriaToEvaluate);

        $this->logger->debug(sprintf('DQI - Evaluation of %d product-models done', $productModelIdsToEvaluate->count()));
    }
}
