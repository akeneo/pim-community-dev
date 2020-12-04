<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\Product;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation\ConsolidateProductScores;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluatePendingCriteria;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class InitializeEvaluationOfAProductSubscriberSpec extends ObjectBehavior
{
    public function let(
        FeatureFlag $dataQualityInsightsFeature,
        CreateCriteriaEvaluations $createProductsCriteriaEvaluations,
        LoggerInterface $logger,
        EvaluatePendingCriteria $evaluatePendingCriteria,
        ConsolidateProductScores $consolidateProductScores
    ) {
        $this->beConstructedWith(
            $dataQualityInsightsFeature,
            $createProductsCriteriaEvaluations,
            $logger,
            $evaluatePendingCriteria,
            $consolidateProductScores
        );
    }

    public function it_is_an_event_subscriber(): void
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_subscribes_to_several_events(): void
    {
        $this::getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_SAVE);
    }

    public function it_does_nothing_when_the_entity_is_not_a_product($dataQualityInsightsFeature, $createProductsCriteriaEvaluations)
    {
        $dataQualityInsightsFeature->isEnabled()->shouldNotBeCalled();
        $createProductsCriteriaEvaluations->createAll(Argument::any())->shouldNotBeCalled();
        $this->onPostSave(new GenericEvent(new \stdClass()));
    }

    public function it_does_nothing_when_data_quality_insights_feature_is_not_active(
        $dataQualityInsightsFeature,
        $createProductsCriteriaEvaluations,
        ProductInterface $product
    ) {
        $dataQualityInsightsFeature->isEnabled()->willReturn(false);
        $createProductsCriteriaEvaluations->createAll(Argument::any())->shouldNotBeCalled();

        $this->onPostSave(new GenericEvent($product->getWrappedObject()));
    }

    public function it_does_nothing_on_non_unitary_post_save(
        $dataQualityInsightsFeature,
        $createProductsCriteriaEvaluations,
        ProductInterface $product
    ): void {
        $dataQualityInsightsFeature->isEnabled()->shouldNotBeCalled();
        $createProductsCriteriaEvaluations->createAll(Argument::any())->shouldNotBeCalled();

        $this->onPostSave(new GenericEvent($product->getWrappedObject(), ['unitary' => false]));
        $this->onPostSave(new GenericEvent($product->getWrappedObject(), []));
    }

    public function it_creates_criteria_on_unitary_product_post_save(
        $dataQualityInsightsFeature,
        $createProductsCriteriaEvaluations,
        $evaluatePendingCriteria,
        $consolidateProductScores,
        ProductInterface $product
    ) {
        $product->getId()->willReturn(12345);
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);
        $createProductsCriteriaEvaluations->createAll([new ProductId(12345)])->shouldBeCalled();

        $evaluatePendingCriteria->evaluateSynchronousCriteria([12345])->shouldBeCalled();
        $consolidateProductScores->consolidate([12345])->shouldBeCalled();

        $this->onPostSave(new GenericEvent($product->getWrappedObject(), ['unitary' => true]));
    }

    public function it_does_not_stop_the_process_if_something_goes_wrong(
        $dataQualityInsightsFeature,
        $createProductsCriteriaEvaluations,
        $logger,
        ProductInterface $product
    ) {
        $product->getId()->willReturn(12345);
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);
        $createProductsCriteriaEvaluations->createAll([new ProductId(12345)])->willThrow(\Exception::class);

        $logger->error('Unable to create product criteria evaluation', Argument::any())->shouldBeCalledOnce();

        $this->onPostSave(new GenericEvent($product->getWrappedObject(), ['unitary' => true]));
    }
}
