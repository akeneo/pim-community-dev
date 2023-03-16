<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Messenger;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CriteriaByFeatureRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProductModels;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProducts;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Tool\Component\Messenger\TraceableMessageHandlerInterface;
use Akeneo\Tool\Component\Messenger\TraceableMessageInterface;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LaunchProductAndProductModelEvaluationsHandler implements TraceableMessageHandlerInterface
{
    public function __construct(
        private readonly CriteriaByFeatureRegistry $productCriteriaRegistry,
        private readonly CriteriaByFeatureRegistry $productModelCriteriaRegistry,
        private readonly CreateCriteriaEvaluations $createProductCriteriaEvaluations,
        private readonly CreateCriteriaEvaluations $createProductModelCriteriaEvaluations,
        private readonly EvaluateProducts $evaluateProducts,
        private readonly EvaluateProductModels $evaluateProductModels,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param LaunchProductAndProductModelEvaluationsMessage $message
     */
    public function __invoke(TraceableMessageInterface $message): void
    {
        Assert::isInstanceOf($message, LaunchProductAndProductModelEvaluationsMessage::class);

        $this->logger->debug('Handler ' . get_class($this) . ' received a message: LaunchProductAndProductModelEvaluationsMessage', [
            'correlation_id' => $message->getCorrelationId(),
            'tenant_id' => $message->getTenantId(),
        ]);

        if (!$message->productUuids->isEmpty()) {
            $this->evaluateProducts($message);
        }

        if (!$message->productModelIds->isEmpty()) {
            $this->evaluateProductModels($message);
        }
    }

    private function evaluateProducts(LaunchProductAndProductModelEvaluationsMessage $message): void
    {
        $criteriaToEvaluate = empty($message->criteriaToEvaluate)
            ? $this->productCriteriaRegistry->getAllCriterionCodes()
            : \array_map(fn (string $criterionCode) => new CriterionCode($criterionCode), $message->criteriaToEvaluate);

        $this->createProductCriteriaEvaluations->create($criteriaToEvaluate, $message->productUuids);
        ($this->evaluateProducts)($message->productUuids);

        $this->logger->debug(sprintf('DQI - Evaluation of %d products done', $message->productUuids->count()), [
            'correlation_id' => $message->getCorrelationId(),
            'tenant_id' => $message->getTenantId(),
        ]);
    }

    private function evaluateProductModels(LaunchProductAndProductModelEvaluationsMessage $message): void
    {
        $criteriaToEvaluate = empty($message->criteriaToEvaluate)
            ? $this->productModelCriteriaRegistry->getAllCriterionCodes()
            : \array_map(fn (string $criterionCode) => new CriterionCode($criterionCode), $message->criteriaToEvaluate);

        $this->createProductModelCriteriaEvaluations->create($criteriaToEvaluate, $message->productModelIds);
        ($this->evaluateProductModels)($message->productModelIds);

        $this->logger->debug(sprintf('DQI - Evaluation of %d product models done', $message->productModelIds->count()), [
            'correlation_id' => $message->getCorrelationId(),
            'tenant_id' => $message->getTenantId(),
        ]);
    }
}
