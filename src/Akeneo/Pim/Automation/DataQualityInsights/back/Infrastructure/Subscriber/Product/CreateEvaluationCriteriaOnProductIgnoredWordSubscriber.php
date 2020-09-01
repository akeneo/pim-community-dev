<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\Product;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Events\ProductWordIgnoredEvent;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class CreateEvaluationCriteriaOnProductIgnoredWordSubscriber implements EventSubscriberInterface
{
    /** @var FeatureFlag */
    private $dataQualityInsightsFeature;

    /** @var CreateCriteriaEvaluations */
    private $createProductsCriteriaEvaluations;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        FeatureFlag $dataQualityInsightsFeature,
        CreateCriteriaEvaluations $createProductsCriteriaEvaluations,
        LoggerInterface $logger
    ) {
        $this->dataQualityInsightsFeature = $dataQualityInsightsFeature;
        $this->createProductsCriteriaEvaluations = $createProductsCriteriaEvaluations;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            ProductWordIgnoredEvent::class => 'onIgnoredWord',
        ];
    }

    public function onIgnoredWord(ProductWordIgnoredEvent $event)
    {
        if (! $this->dataQualityInsightsFeature->isEnabled()) {
            return;
        }

        $this->initializeCriteria($event->getProductId()->toInt());
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
