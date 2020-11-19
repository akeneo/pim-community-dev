<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\Product;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation\ConsolidateProductScores;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluatePendingCriteria;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class InitializeEvaluationOfAProductSubscriber implements EventSubscriberInterface
{
    private FeatureFlag $dataQualityInsightsFeature;

    private CreateCriteriaEvaluations $createProductsCriteriaEvaluations;

    private LoggerInterface $logger;

    private EvaluatePendingCriteria $evaluatePendingCriteria;

    private ConsolidateProductScores $consolidateProductScores;

    public function __construct(
        FeatureFlag $dataQualityInsightsFeature,
        CreateCriteriaEvaluations $createProductsCriteriaEvaluations,
        LoggerInterface $logger,
        EvaluatePendingCriteria $evaluatePendingCriteria,
        ConsolidateProductScores $consolidateProductScores
    ) {
        $this->dataQualityInsightsFeature = $dataQualityInsightsFeature;
        $this->createProductsCriteriaEvaluations = $createProductsCriteriaEvaluations;
        $this->logger = $logger;
        $this->evaluatePendingCriteria = $evaluatePendingCriteria;
        $this->consolidateProductScores = $consolidateProductScores;
    }

    public static function getSubscribedEvents()
    {
        return [
            // Priority greater than zero to ensure that the evaluation is done prior to the re-indexation of the product in ES
            StorageEvents::POST_SAVE => ['onPostSave', 10],
        ];
    }

    public function onPostSave(GenericEvent $event): void
    {
        $subject = $event->getSubject();
        if (! $subject instanceof ProductInterface) {
            return;
        }

        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return;
        }

        if (! $this->dataQualityInsightsFeature->isEnabled()) {
            return;
        }

        $productId = intval($subject->getId());
        $this->initializeCriteria($productId);
        $this->evaluatePendingCriteria->evaluateSynchronousCriteria([$productId]);
        $this->consolidateProductScores->consolidate([$productId]);
    }

    private function initializeCriteria($productId)
    {
        try {
            $this->createProductsCriteriaEvaluations->createAll([new ProductId($productId)]);
        } catch (\Throwable $e) {
            $this->logger->error(
                'Unable to create product criteria evaluation',
                [
                    'error_code' => 'unable_to_create_product_criteria_evaluation',
                    'error_message' => $e->getMessage(),
                ]
            );
        }
    }
}
