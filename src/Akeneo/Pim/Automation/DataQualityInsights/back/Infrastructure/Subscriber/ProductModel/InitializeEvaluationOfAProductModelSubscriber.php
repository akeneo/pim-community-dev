<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\ProductModel;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
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
    public function __construct(
        private FeatureFlag                     $dataQualityInsightsFeature,
        private CreateCriteriaEvaluations       $createProductModelCriteriaEvaluations,
        private LoggerInterface                 $logger,
        private ProductEntityIdFactoryInterface $idFactory
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE => 'onPostSave',
        ];
    }

    public function onPostSave(GenericEvent $event): void
    {
        $subject = $event->getSubject();
        if (!$subject instanceof ProductModelInterface) {
            return;
        }

        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return;
        }

        if (!$this->dataQualityInsightsFeature->isEnabled()) {
            return;
        }

        $this->initializeProductModelCriteria(intval($subject->getId()));
    }

    private function initializeProductModelCriteria(int $productModelId): void
    {
        try {
            $productModelIdCollection = $this->idFactory->createCollection([(string) $productModelId]);
            $this->createProductModelCriteriaEvaluations->createAll($productModelIdCollection);
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
