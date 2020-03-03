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
            StorageEvents::POST_SAVE_ALL => 'onPostSaveAll',
        ];
    }

    public function onIgnoredWord(WordIgnoredEvent $event)
    {
        if (! $this->dataQualityInsightsFeature->isEnabled()) {
            return;
        }

        $this->initializeCriteria([$event->getProductId()->toInt()]);
    }

    public function onIgnoredTitleSuggestion(TitleSuggestionIgnoredEvent $event)
    {
        if (! $this->dataQualityInsightsFeature->isEnabled()) {
            return;
        }

        $this->initializeCriteria([$event->getProductId()->toInt()]);
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

        $productIds = [intval($subject->getId())];
        $this->initializeCriteria($productIds);
        $this->evaluatePendingCriteria->evaluateSynchronousCriteria($productIds);
        $this->consolidateProductAxisRates->consolidate($productIds);
        $this->indexProductRates->execute($productIds);
    }

    public function onPostSaveAll(GenericEvent $event): void
    {
        $subjects = $event->getSubject();
        if (! is_array($subjects)) {
            return;
        }

        if (! $this->dataQualityInsightsFeature->isEnabled()) {
            return;
        }

        $productIds = $this->getProductIds($subjects);
        if (empty($productIds)) {
            return;
        }

        $this->initializeCriteria($productIds);
    }

    private function getProductIds($subjects): array
    {
        $productIds = [];
        foreach ($subjects as $subject) {
            if (! $subject instanceof ProductInterface) {
                continue;
            }
            $productIds[] = intval($subject->getId());
        }

        return $productIds;
    }

    private function initializeCriteria(array $productIds)
    {
        try {
            $this->createProductsCriteriaEvaluations->create(
                array_map(function (int $productId) {
                    return new ProductId($productId);
                }, $productIds)
            );
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
