<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\ProductModel;

use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\CreateProductsCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Application\FeatureFlag;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\GetDescendantVariantProductIds;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

final class InitializeVariantProductsEvaluationsSubscriberSpec extends ObjectBehavior
{
    public function let(
        FeatureFlag $dataQualityInsightsFeature,
        CreateProductsCriteriaEvaluations $createProductsCriteriaEvaluations,
        LoggerInterface $logger,
        GetDescendantVariantProductIds $getDescendantVariantProductIds
    ) {
        $this->beConstructedWith($dataQualityInsightsFeature, $createProductsCriteriaEvaluations, $logger, $getDescendantVariantProductIds);
    }

    public function it_is_an_event_subscriber(): void
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_subscribes_to_several_events(): void
    {
        $this::getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_SAVE);
        $this::getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_SAVE_ALL);
    }

    public function it_does_nothing_when_the_entity_is_not_a_product_model($dataQualityInsightsFeature, $createProductsCriteriaEvaluations)
    {
        $dataQualityInsightsFeature->isEnabled()->shouldNotBeCalled();
        $createProductsCriteriaEvaluations->create(Argument::any())->shouldNotBeCalled();
        $this->onPostSave(new GenericEvent(new \stdClass()));
    }

    public function it_does_nothing_when_data_quality_insights_feature_is_not_active(
        $dataQualityInsightsFeature,
        $createProductsCriteriaEvaluations,
        ProductModelInterface $productModel
    ) {
        $dataQualityInsightsFeature->isEnabled()->willReturn(false);
        $createProductsCriteriaEvaluations->create(Argument::any())->shouldNotBeCalled();
        $this->onPostSave(new GenericEvent($productModel->getWrappedObject()));
    }

    public function it_does_nothing_on_non_unitary_post_save(
        $dataQualityInsightsFeature,
        $createProductsCriteriaEvaluations,
        ProductModelInterface $productModel
    ): void {
        $dataQualityInsightsFeature->isEnabled()->shouldNotBeCalled();
        $createProductsCriteriaEvaluations->create(Argument::any())->shouldNotBeCalled();

        $this->onPostSave(new GenericEvent($productModel->getWrappedObject(), ['unitary' => false]));
        $this->onPostSave(new GenericEvent($productModel->getWrappedObject(), []));
    }

    public function it_creates_criteria_on_unitary_product_model_post_save(
        $dataQualityInsightsFeature,
        $createProductsCriteriaEvaluations,
        $getDescendantVariantProductIds,
        ProductModelInterface $productModel
    ) {
        $productModel->getId()->willReturn(12345);
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);
        $getDescendantVariantProductIds->fromProductModelIds([12345])->willReturn([6789]);
        $createProductsCriteriaEvaluations->create([new ProductId(6789)])->shouldBeCalled();

        $this->onPostSave(new GenericEvent($productModel->getWrappedObject(), ['unitary' => true]));
    }

    public function it_does_nothing_when_data_quality_insights_is_not_active_on_post_save_all(
        $dataQualityInsightsFeature,
        $createProductsCriteriaEvaluations,
        $getDescendantVariantProductIds,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2
    ) {
        $dataQualityInsightsFeature->isEnabled()->willReturn(false);
        $createProductsCriteriaEvaluations->create(Argument::any())->shouldNotBeCalled();
        $getDescendantVariantProductIds->fromProductModelIds(Argument::any())->shouldNotBeCalled();

        $this->onPostSaveAll(new GenericEvent([$productModel1->getWrappedObject(), $productModel2->getWrappedObject()]));
    }

    public function it_creates_criteria_on_post_save_all(
        $dataQualityInsightsFeature,
        $createProductsCriteriaEvaluations,
        $getDescendantVariantProductIds,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2
    ) {
        $productModel1->getId()->willReturn(12345);
        $productModel2->getId()->willReturn(6789);
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);
        $getDescendantVariantProductIds->fromProductModelIds([12345, 6789])->willReturn([111, 222]);
        $createProductsCriteriaEvaluations->create([new ProductId(111), new ProductId(222)])->shouldBeCalled();

        $this->onPostSaveAll(new GenericEvent([$productModel1->getWrappedObject(), $productModel2->getWrappedObject()]));
    }
}
