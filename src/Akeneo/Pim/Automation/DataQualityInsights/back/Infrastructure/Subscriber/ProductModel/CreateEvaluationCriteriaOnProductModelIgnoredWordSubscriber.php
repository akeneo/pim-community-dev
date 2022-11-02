<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\ProductModel;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Events\ProductModelWordIgnoredEvent;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetDescendantVariantProductUuidsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\DescendantProductModelIdsQueryInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CreateEvaluationCriteriaOnProductModelIgnoredWordSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private FeatureFlag                                  $dataQualityInsightsFeature,
        private CreateCriteriaEvaluations                    $createProductModelCriteriaEvaluations,
        private LoggerInterface                              $logger,
        private GetDescendantVariantProductUuidsQueryInterface $getDescendantVariantProductUuidsQuery,
        private DescendantProductModelIdsQueryInterface      $getDescendantProductModelIdsQuery,
        private CreateCriteriaEvaluations                    $createProductsCriteriaEvaluations,
        private ProductEntityIdFactoryInterface              $productModelIdFactory,
        private ProductEntityIdFactoryInterface              $productIdFactory
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductModelWordIgnoredEvent::class => 'onIgnoredWord',
        ];
    }

    public function onIgnoredWord(ProductModelWordIgnoredEvent $event)
    {
        if (!$this->dataQualityInsightsFeature->isEnabled()) {
            return;
        }

        $this->initializeProductModelCriteria($event->getProductId());
        $this->initializeCriteriaForSubProductModels($event->getProductId());
        $this->initializeCriteriaForVariantProducts($event->getProductId());
    }

    private function initializeProductModelCriteria(ProductEntityIdInterface $productModelId)
    {
        try {
            $this->createProductModelCriteriaEvaluations->createAll(
                $this->productModelIdFactory->createCollection([(string)$productModelId])
            );
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

    private function initializeCriteriaForSubProductModels(ProductEntityIdInterface $productId)
    {
        $subProductModelIds = $this->getDescendantProductModelIdsQuery->fetchFromParentProductModelId((int)(string)$productId);

        foreach ($subProductModelIds as $subProductModelId) {
            $this->initializeProductModelCriteria(
                $this->productModelIdFactory->create((string)$subProductModelId)
            );
        }
    }

    private function initializeCriteriaForVariantProducts(ProductEntityIdInterface $productModelId): void
    {
        $variantProductIds = $this->getDescendantVariantProductUuidsQuery->fromProductModelIds(
            $this->productModelIdFactory->createCollection([(string)$productModelId])
        );

        try {
            $this->createProductsCriteriaEvaluations->createAll(
                $this->productIdFactory->createCollection($variantProductIds)
            );
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
