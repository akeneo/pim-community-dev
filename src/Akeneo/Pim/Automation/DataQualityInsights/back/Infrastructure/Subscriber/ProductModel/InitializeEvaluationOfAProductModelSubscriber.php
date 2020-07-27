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

use Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation\ConsolidateAxesRates;
use Akeneo\Pim\Automation\DataQualityInsights\Application\FeatureFlag;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluatePendingCriteria;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Events\ProductModelWordIgnoredEvent;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetDescendantVariantProductIdsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\DescendantProductModelIdsQueryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class InitializeEvaluationOfAProductModelSubscriber implements EventSubscriberInterface
{
    /** @var FeatureFlag */
    private $dataQualityInsightsFeature;

    /** @var CreateCriteriaEvaluations */
    private $createProductModelCriteriaEvaluations;

    /** @var LoggerInterface */
    private $logger;

    /** @var EvaluatePendingCriteria */
    private $evaluatePendingCriteria;

    /** @var ConsolidateAxesRates */
    private $consolidateAxesRates;

    /** @var GetDescendantVariantProductIdsQueryInterface */
    private $getDescendantVariantProductIdsQuery;

    /** @var DescendantProductModelIdsQueryInterface */
    private $getDescendantProductModelIdsQuery;

    /** @var CreateCriteriaEvaluations */
    private $createProductsCriteriaEvaluations;

    public function __construct(
        FeatureFlag $dataQualityInsightsFeature,
        CreateCriteriaEvaluations $createProductModelCriteriaEvaluations,
        LoggerInterface $logger,
        EvaluatePendingCriteria $evaluatePendingCriteria,
        ConsolidateAxesRates $consolidateAxesRates,
        GetDescendantVariantProductIdsQueryInterface $getDescendantVariantProductIdsQuery,
        DescendantProductModelIdsQueryInterface $getDescendantProductModelIdsQuery,
        CreateCriteriaEvaluations $createProductsCriteriaEvaluations
    ) {
        $this->dataQualityInsightsFeature = $dataQualityInsightsFeature;
        $this->createProductModelCriteriaEvaluations = $createProductModelCriteriaEvaluations;
        $this->logger = $logger;
        $this->evaluatePendingCriteria = $evaluatePendingCriteria;
        $this->consolidateAxesRates = $consolidateAxesRates;
        $this->getDescendantVariantProductIdsQuery = $getDescendantVariantProductIdsQuery;
        $this->getDescendantProductModelIdsQuery = $getDescendantProductModelIdsQuery;
        $this->createProductsCriteriaEvaluations = $createProductsCriteriaEvaluations;
    }

    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::POST_SAVE => 'onPostSave',
            ProductModelWordIgnoredEvent::class => 'onIgnoredWord',
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
        $this->consolidateAxesRates->consolidate([$productModelId]);
    }

    public function onIgnoredWord(ProductModelWordIgnoredEvent $event)
    {
        if (! $this->dataQualityInsightsFeature->isEnabled()) {
            return;
        }

        $this->initializeProductModelCriteria($event->getProductId()->toInt());
        $this->initializeCriteriaForSubProductModels($event->getProductId());
        $this->initializeCriteriaForVariantProducts($event->getProductId());
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

    private function initializeCriteriaForSubProductModels(ProductId $productId)
    {
        $subProductModelIds = $this->getDescendantProductModelIdsQuery->fetchFromParentProductModelId($productId->toInt());
        foreach ($subProductModelIds as $subProductModelId) {
            $this->initializeProductModelCriteria($subProductModelId);
        }
    }

    private function initializeCriteriaForVariantProducts(ProductId $productId): void
    {
        $variantProductIds = $this->getDescendantVariantProductIdsQuery->fromProductModelIds([$productId->toInt()]);
        foreach ($variantProductIds as $variantProductId) {
            try {
                $this->createProductsCriteriaEvaluations->createAll([new ProductId((int) $variantProductId)]);
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
}
