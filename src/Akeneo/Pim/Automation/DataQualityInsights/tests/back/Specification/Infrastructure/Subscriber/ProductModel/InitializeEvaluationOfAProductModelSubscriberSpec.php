<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\ProductModel;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class InitializeEvaluationOfAProductModelSubscriberSpec extends ObjectBehavior
{
    public function let(
        FeatureFlag                     $dataQualityInsightsFeature,
        CreateCriteriaEvaluations       $createCriteriaEvaluations,
        LoggerInterface                 $logger,
        ProductEntityIdFactoryInterface $idFactory
    )
    {
        $this->beConstructedWith(
            $dataQualityInsightsFeature,
            $createCriteriaEvaluations,
            $logger,
            $idFactory
        );
    }

    public function it_is_an_event_subscriber(): void
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_subscribes_to_events()
    {
        $this::getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_SAVE);
    }

    public function it_does_nothing_when_the_entity_is_not_a_product($dataQualityInsightsFeature, $createCriteriaEvaluations)
    {
        $dataQualityInsightsFeature->isEnabled()->shouldNotBeCalled();
        $createCriteriaEvaluations->createAll(Argument::any())->shouldNotBeCalled();
        $this->onPostSave(new GenericEvent(new \stdClass()));
    }

    public function it_does_nothing_when_data_quality_insights_feature_is_not_active(
        $dataQualityInsightsFeature,
        $createCriteriaEvaluations,
        ProductModelInterface $productModel
    )
    {
        $dataQualityInsightsFeature->isEnabled()->willReturn(false);
        $createCriteriaEvaluations->createAll(Argument::any())->shouldNotBeCalled();

        $this->onPostSave(new GenericEvent($productModel->getWrappedObject()));
    }

    public function it_does_nothing_on_non_unitary_post_save(
        $dataQualityInsightsFeature,
        $createCriteriaEvaluations,
        ProductModelInterface $productModel
    ): void
    {
        $dataQualityInsightsFeature->isEnabled()->shouldNotBeCalled();
        $createCriteriaEvaluations->createAll(Argument::any())->shouldNotBeCalled();

        $this->onPostSave(new GenericEvent($productModel->getWrappedObject(), ['unitary' => false]));
        $this->onPostSave(new GenericEvent($productModel->getWrappedObject(), []));
    }

    public function it_creates_criteria_on_unitary_product_post_save(
        $dataQualityInsightsFeature,
        $createCriteriaEvaluations,
        $idFactory,
        ProductModelInterface $productModel
    )
    {
        $productModelId = 12345;
        $productModelIdCollection = ProductModelIdCollection::fromStrings([(string)$productModelId]);

        $productModel->getId()->willReturn($productModelId);
        $idFactory->createCollection([(string)$productModelId])->willReturn($productModelIdCollection);
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);
        $createCriteriaEvaluations->createAll($productModelIdCollection)->shouldBeCalled();

        $this->onPostSave(new GenericEvent($productModel->getWrappedObject(), ['unitary' => true]));
    }

    public function it_does_not_stop_the_process_if_something_goes_wrong(
        $dataQualityInsightsFeature,
        $createCriteriaEvaluations,
        $logger,
        $idFactory,
        ProductModelInterface $productModel
    )
    {
        $productModelId = 12345;
        $productModelIdCollection = ProductModelIdCollection::fromStrings([(string)$productModelId]);

        $productModel->getId()->willReturn($productModelId);
        $idFactory->createCollection([(string)$productModelId])->willReturn($productModelIdCollection);
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);
        $createCriteriaEvaluations->createAll($productModelIdCollection)->willThrow(\Exception::class);

        $logger->error('Unable to create product model criteria evaluation', Argument::any())->shouldBeCalledOnce();

        $this->onPostSave(new GenericEvent($productModel->getWrappedObject(), ['unitary' => true]));
    }
}
