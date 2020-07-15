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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\Product;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ConsolidateProductAxisRates;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\CreateProductsCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\EvaluatePendingCriteria;
use Akeneo\Pim\Automation\DataQualityInsights\Application\FeatureFlag;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\IndexProductRates;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Events\WordIgnoredEvent;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
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
        CreateProductsCriteriaEvaluations $createProductsCriteriaEvaluations,
        LoggerInterface $logger,
        EvaluatePendingCriteria $evaluatePendingCriteria,
        ConsolidateProductAxisRates $consolidateProductAxisRates,
        IndexProductRates $indexProductRates
    ) {
        $this->beConstructedWith(
            $dataQualityInsightsFeature,
            $createProductsCriteriaEvaluations,
            $logger,
            $evaluatePendingCriteria,
            $consolidateProductAxisRates,
            $indexProductRates
        );
    }

    public function it_is_an_event_subscriber(): void
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_subscribes_to_several_events(): void
    {
        $this::getSubscribedEvents()->shouldHaveKey(WordIgnoredEvent::WORD_IGNORED);
        $this::getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_SAVE);
    }

    public function it_schedule_evaluation_when_a_word_is_ignored(
        $dataQualityInsightsFeature,
        $createProductsCriteriaEvaluations,
        ProductInterface $product
    ) {
        $product->getId()->willReturn(12345);
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);
        $createProductsCriteriaEvaluations->create([new ProductId(12345)])->shouldBeCalled();

        $this->onIgnoredWord(new WordIgnoredEvent(new ProductId(12345)));
    }

    public function it_does_nothing_when_the_entity_is_not_a_product($dataQualityInsightsFeature, $createProductsCriteriaEvaluations)
    {
        $dataQualityInsightsFeature->isEnabled()->shouldNotBeCalled();
        $createProductsCriteriaEvaluations->create(Argument::any())->shouldNotBeCalled();
        $this->onPostSave(new GenericEvent(new \stdClass()));
    }

    public function it_does_nothing_when_data_quality_insights_feature_is_not_active(
        $dataQualityInsightsFeature,
        $createProductsCriteriaEvaluations,
        ProductInterface $product
    ) {
        $dataQualityInsightsFeature->isEnabled()->willReturn(false);
        $createProductsCriteriaEvaluations->create(Argument::any())->shouldNotBeCalled();
        $product->isVariant()->willReturn(false);

        $this->onPostSave(new GenericEvent($product->getWrappedObject()));
    }

    public function it_does_nothing_when_product_is_a_variant(
        $dataQualityInsightsFeature,
        $createProductsCriteriaEvaluations,
        ProductInterface $product
    ) {
        $dataQualityInsightsFeature->isEnabled()->willReturn(false);
        $createProductsCriteriaEvaluations->create(Argument::any())->shouldNotBeCalled();
        $product->isVariant()->willReturn(true);

        $this->onPostSave(new GenericEvent($product->getWrappedObject()));
    }

    public function it_does_nothing_on_non_unitary_post_save(
        $dataQualityInsightsFeature,
        $createProductsCriteriaEvaluations,
        ProductInterface $product
    ): void {
        $dataQualityInsightsFeature->isEnabled()->shouldNotBeCalled();
        $createProductsCriteriaEvaluations->create(Argument::any())->shouldNotBeCalled();
        $product->isVariant()->willReturn(false);

        $this->onPostSave(new GenericEvent($product->getWrappedObject(), ['unitary' => false]));
        $this->onPostSave(new GenericEvent($product->getWrappedObject(), []));
    }

    public function it_creates_criteria_on_unitary_product_post_save(
        $dataQualityInsightsFeature,
        $createProductsCriteriaEvaluations,
        $evaluatePendingCriteria,
        $consolidateProductAxisRates,
        $indexProductRates,
        ProductInterface $product
    ) {
        $product->getId()->willReturn(12345);
        $product->isVariant()->willReturn(false);
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);
        $createProductsCriteriaEvaluations->create([new ProductId(12345)])->shouldBeCalled();

        $evaluatePendingCriteria->evaluateSynchronousCriteria([12345])->shouldBeCalled();
        $consolidateProductAxisRates->consolidate([12345])->shouldBeCalled();
        $indexProductRates->execute([12345])->shouldBeCalled();

        $this->onPostSave(new GenericEvent($product->getWrappedObject(), ['unitary' => true]));
    }

    public function it_does_not_stop_the_process_if_something_goes_wrong(
        $dataQualityInsightsFeature,
        $createProductsCriteriaEvaluations,
        $logger,
        ProductInterface $product
    ) {
        $product->getId()->willReturn(12345);
        $product->isVariant()->willReturn(false);
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);
        $createProductsCriteriaEvaluations->create([new ProductId(12345)])->willThrow(\Exception::class);

        $logger->error('Unable to create product criteria evaluation', Argument::any())->shouldBeCalledOnce();

        $this->onPostSave(new GenericEvent($product->getWrappedObject(), ['unitary' => true]));
    }
}
