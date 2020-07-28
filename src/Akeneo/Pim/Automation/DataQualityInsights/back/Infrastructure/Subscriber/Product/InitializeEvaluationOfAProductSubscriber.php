<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\Product;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ConsolidateProductAxisRates;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\CreateProductsCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\EvaluatePendingCriteria;
use Akeneo\Pim\Automation\DataQualityInsights\Application\FeatureFlag;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\IndexProductRates;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Events\TitleSuggestionIgnoredEvent;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Events\WordIgnoredEvent;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

final class InitializeEvaluationOfAProductSubscriber implements EventSubscriberInterface
{
    /** @var FeatureFlag */
    private $dataQualityInsightsFeature;

    /** @var CreateProductsCriteriaEvaluations */
    private $createProductsCriteriaEvaluations;

    /** @var LoggerInterface */
    private $logger;

    /** @var EvaluatePendingCriteria */
    private $evaluatePendingCriteria;

    /** @var ConsolidateProductAxisRates */
    private $consolidateProductAxisRates;

    /** @var IndexProductRates */
    private $indexProductRates;

    public function __construct(
        FeatureFlag $dataQualityInsightsFeature,
        CreateProductsCriteriaEvaluations $createProductsCriteriaEvaluations,
        LoggerInterface $logger,
        EvaluatePendingCriteria $evaluatePendingCriteria,
        ConsolidateProductAxisRates $consolidateProductAxisRates,
        IndexProductRates $indexProductRates
    ) {
        $this->dataQualityInsightsFeature = $dataQualityInsightsFeature;
        $this->createProductsCriteriaEvaluations = $createProductsCriteriaEvaluations;
        $this->logger = $logger;
        $this->evaluatePendingCriteria = $evaluatePendingCriteria;
        $this->consolidateProductAxisRates = $consolidateProductAxisRates;
        $this->indexProductRates = $indexProductRates;
    }

    public static function getSubscribedEvents()
    {
        return [
            WordIgnoredEvent::WORD_IGNORED => 'onIgnoredWord',
            TitleSuggestionIgnoredEvent::TITLE_SUGGESTION_IGNORED => 'onIgnoredTitleSuggestion',
            StorageEvents::POST_SAVE => 'onPostSave',
        ];
    }

    public function onIgnoredWord(WordIgnoredEvent $event)
    {
        if (! $this->dataQualityInsightsFeature->isEnabled()) {
            return;
        }

        $this->initializeCriteria($event->getProductId()->toInt());
    }

    public function onIgnoredTitleSuggestion(TitleSuggestionIgnoredEvent $event)
    {
        if (! $this->dataQualityInsightsFeature->isEnabled()) {
            return;
        }

        $this->initializeCriteria($event->getProductId()->toInt());
    }

    public function onPostSave(GenericEvent $event): void
    {
        $subject = $event->getSubject();
        if (! $subject instanceof ProductInterface || $subject->isVariant() === true) {
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
        $this->consolidateProductAxisRates->consolidate([$productId]);
        $this->indexProductRates->execute([$productId]);
    }

    private function initializeCriteria($productId)
    {
        try {
            $this->createProductsCriteriaEvaluations->create([new ProductId($productId)]);
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
