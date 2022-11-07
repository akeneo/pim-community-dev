<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\Product;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Events\ProductWordIgnoredEvent;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class CreateEvaluationCriteriaOnProductIgnoredWordSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private FeatureFlag                     $dataQualityInsightsFeature,
        private CreateCriteriaEvaluations       $createProductsCriteriaEvaluations,
        private LoggerInterface                 $logger,
        private ProductEntityIdFactoryInterface $idFactory
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductWordIgnoredEvent::class => 'onIgnoredWord',
        ];
    }

    public function onIgnoredWord(ProductWordIgnoredEvent $event)
    {
        if (!$this->dataQualityInsightsFeature->isEnabled()) {
            return;
        }

        $productIdCollection = $this->idFactory->createCollection([(string)$event->getProductId()]);

        try {
            $this->createProductsCriteriaEvaluations->createAll($productIdCollection);
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
