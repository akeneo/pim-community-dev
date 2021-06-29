<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\ProductModel;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluatePendingCriteria;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InitializeEvaluationOfAProductModelSubscriber implements EventSubscriberInterface
{
    private FeatureFlag $dataQualityInsightsFeature;

    private CreateCriteriaEvaluations $createProductModelCriteriaEvaluations;

    private LoggerInterface $logger;

    private EvaluatePendingCriteria $evaluatePendingCriteria;

    public function __construct(
        FeatureFlag $dataQualityInsightsFeature,
        CreateCriteriaEvaluations $createProductModelCriteriaEvaluations,
        LoggerInterface $logger,
        EvaluatePendingCriteria $evaluatePendingCriteria
    ) {
        $this->dataQualityInsightsFeature = $dataQualityInsightsFeature;
        $this->createProductModelCriteriaEvaluations = $createProductModelCriteriaEvaluations;
        $this->logger = $logger;
        $this->evaluatePendingCriteria = $evaluatePendingCriteria;
    }

    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::POST_SAVE => 'onPostSave',
        ];
    }

    public function onPostSave(GenericEvent $event): void
    {
        $subject = $event->getSubject();
        if (! $subject instanceof ProductModelInterface) {
            return;
        }

        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return;
        }

        if (! $this->dataQualityInsightsFeature->isEnabled()) {
            return;
        }

        $productModelId = intval($subject->getId());
        $this->initializeProductModelCriteria($productModelId);
        $this->evaluatePendingCriteria->evaluateSynchronousCriteria([$productModelId]);
    }

    private function initializeProductModelCriteria($productModelId)
    {
        try {
            $this->createProductModelCriteriaEvaluations->createAll([new ProductId($productModelId)]);
        } catch (\Throwable $e) {
            $this->logger->error(
                'Unable to create product model criteria evaluation',
                [
                    'error_code' => 'unable_to_create_product_model_criteria_evaluation',
                    'error_message' => $e->getMessage(),
                ]
            );
        }
    }
}
