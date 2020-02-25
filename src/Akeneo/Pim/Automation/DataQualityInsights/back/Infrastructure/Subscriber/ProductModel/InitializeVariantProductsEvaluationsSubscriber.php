<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\ProductModel;

use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\CreateProductsCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Application\FeatureFlag;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetDescendantVariantProductIdsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

final class InitializeVariantProductsEvaluationsSubscriber implements EventSubscriberInterface
{
    /** @var FeatureFlag */
    private $dataQualityInsightsFeature;

    /** @var CreateProductsCriteriaEvaluations */
    private $createProductsCriteriaEvaluations;

    /** @var LoggerInterface */
    private $logger;

    /** @var GetDescendantVariantProductIdsQueryInterface */
    private $getDescendantVariantProductIds;

    public function __construct(
        FeatureFlag $dataQualityInsightsFeature,
        CreateProductsCriteriaEvaluations $createProductsCriteriaEvaluations,
        LoggerInterface $logger,
        GetDescendantVariantProductIdsQueryInterface $getDescendantVariantProductIds

    ) {
        $this->dataQualityInsightsFeature = $dataQualityInsightsFeature;
        $this->createProductsCriteriaEvaluations = $createProductsCriteriaEvaluations;
        $this->logger = $logger;
        $this->getDescendantVariantProductIds = $getDescendantVariantProductIds;
    }

    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::POST_SAVE => 'onPostSave',
            StorageEvents::POST_SAVE_ALL => 'onPostSaveAll',
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

        $variantProductIds = $this->getDescendantVariantProductIds->fromProductModelIds([$subject->getId()]);
        $this->initializeCriteria($variantProductIds);
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

        $productModelIds = $this->getProductModelIds($subjects);
        if (empty($productModelIds)) {
            return;
        }

        $variantProductIds = $this->getDescendantVariantProductIds->fromProductModelIds($productModelIds);
        $this->initializeCriteria($variantProductIds);
    }

    private function getProductModelIds($subjects): array
    {
        $productModelIds = [];
        foreach ($subjects as $subject) {
            if (! $subject instanceof ProductModelInterface) {
                continue;
            }
            $productModelIds[] = intval($subject->getId());
        }

        return $productModelIds;
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
