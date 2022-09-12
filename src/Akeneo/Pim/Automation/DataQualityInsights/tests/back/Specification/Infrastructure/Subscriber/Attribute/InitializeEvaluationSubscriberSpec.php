<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\Attribute;

use Akeneo\Pim\Automation\DataQualityInsights\Application\StructureEvaluation\EvaluateUpdatedAttributeOptions;
use Akeneo\Pim\Automation\DataQualityInsights\Application\StructureEvaluation\EvaluateUpdatedAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Events\AttributeOptionWordIgnoredEvent;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Events\AttributeWordIgnoredEvent;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeOptionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\AttributeOptionSpellcheckRepository;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\AttributeSpellcheckRepository;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

final class InitializeEvaluationSubscriberSpec extends ObjectBehavior
{
    public function let(
        EvaluateUpdatedAttributes           $evaluateUpdatedAttributes,
        EvaluateUpdatedAttributeOptions     $evaluateUpdatedAttributeOptions,
        FeatureFlag                         $dataQualityInsightsFeature,
        AttributeSpellcheckRepository       $attributeSpellcheckRepository,
        AttributeOptionSpellcheckRepository $attributeOptionSpellcheckRepository,
    )
    {
        $this->beConstructedWith(
            $evaluateUpdatedAttributes,
            $evaluateUpdatedAttributeOptions,
            $dataQualityInsightsFeature,
            $attributeSpellcheckRepository,
            $attributeOptionSpellcheckRepository
        );
    }

    public function it_is_an_event_subscriber(): void
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_subscribes_to_several_events(): void
    {
        $this::getSubscribedEvents()->shouldHaveKey(AttributeWordIgnoredEvent::class);
        $this::getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_SAVE);
        $this::getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_REMOVE);
    }

    public function it_evaluates_attribute_on_ignored_event(
        $evaluateUpdatedAttributes,
        AttributeWordIgnoredEvent $event
    ): void
    {
        $attributeCode = new AttributeCode('attr_code');

        $event->getAttributeCode()->willReturn($attributeCode);
        $evaluateUpdatedAttributes->evaluate($attributeCode)->shouldBeCalledOnce();

        $this->onIgnoredWord($event);
    }

    public function it_evaluates_attribute_option_on_ignored_option_event(
        $evaluateUpdatedAttributeOptions,
        AttributeOptionWordIgnoredEvent $event
    ): void
    {
        $attributeCode = new AttributeCode('attr_code');
        $attributeOptionCode = new AttributeOptionCode($attributeCode, 'attr_code');

        $event->getAttributeOptionCode()->willReturn($attributeOptionCode);
        $evaluateUpdatedAttributeOptions->evaluate($attributeOptionCode)->shouldBeCalledOnce();

        $this->onIgnoredOptionWord($event);
    }

    public function it_does_nothing_when_the_entity_is_not_an_attribute_post_save(
        $evaluateUpdatedAttributes
    ): void
    {
        $evaluateUpdatedAttributes->evaluate(Argument::any())->shouldNotBeCalled();

        $this->onPostSave(new GenericEvent(new \stdClass()));
    }

    public function it_does_nothing_when_the_entity_is_not_an_attribute_option_post_remove(
        $attributeOptionSpellcheckRepository
    ): void
    {
        $attributeOptionSpellcheckRepository->deleteUnknownAttributeOption(Argument::any())->shouldNotBeCalled();

        $this->onPostRemove(new GenericEvent(new \stdClass()));
    }

    public function it_does_nothing_on_non_unitary_post_save(
        $evaluateUpdatedAttributes,
        AttributeInterface $attribute
    ): void
    {
        $evaluateUpdatedAttributes->evaluate(Argument::any())->shouldNotBeCalled();

        $this->onPostSave(new GenericEvent($attribute->getWrappedObject(), ['unitary' => false]));
        $this->onPostSave(new GenericEvent($attribute->getWrappedObject(), []));
    }

    public function it_does_nothing_on_non_unitary_post_remove(
        $attributeOptionSpellcheckRepository,
        AttributeOptionInterface $attributeOption
    ): void
    {
        $attributeOptionSpellcheckRepository->deleteUnknownAttributeOption(Argument::any())->shouldNotBeCalled();

        $this->onPostRemove(new GenericEvent($attributeOption->getWrappedObject(), ['unitary' => false]));
        $this->onPostRemove(new GenericEvent($attributeOption->getWrappedObject(), []));
    }

    public function it_does_nothing_when_data_quality_insights_feature_is_not_active_post_save(
        $evaluateUpdatedAttributes,
        $dataQualityInsightsFeature,
        AttributeInterface $attribute
    ): void
    {
        $dataQualityInsightsFeature->isEnabled()->willReturn(false);
        $evaluateUpdatedAttributes->evaluate(Argument::any())->shouldNotBeCalled();

        $this->onPostSave(new GenericEvent($attribute->getWrappedObject(), ['unitary' => true]));
    }

    public function it_does_nothing_when_data_quality_insights_feature_is_not_active_post_remove(
        $attributeOptionSpellcheckRepository,
        $dataQualityInsightsFeature,
        AttributeInterface $attribute
    ): void
    {
        $dataQualityInsightsFeature->isEnabled()->willReturn(false);
        $attributeOptionSpellcheckRepository->deleteUnknownAttributeOption(Argument::any())->shouldNotBeCalled();

        $this->onPostRemove(new GenericEvent($attribute->getWrappedObject(), ['unitary' => true]));
    }

    public function it_evaluates_on_unitary_attribute_post_save(
        $evaluateUpdatedAttributes,
        $dataQualityInsightsFeature,
        AttributeInterface $attribute
    ): void
    {
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);
        $attribute->getCode()->willReturn('attr_code');
        $evaluateUpdatedAttributes->evaluate(new AttributeCode('attr_code'))->shouldBeCalled();

        $this->onPostSave(new GenericEvent($attribute->getWrappedObject(), ['unitary' => true]));
    }

    public function it_evaluates_on_unitary_attribute_option_post_save(
        $evaluateUpdatedAttributeOptions,
        $dataQualityInsightsFeature,
        AttributeInterface $attribute,
        AttributeOptionInterface $attributeOption
    ): void
    {
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);
        $attribute->getCode()->willReturn('attr_code');
        $attributeOption->getAttribute()->willReturn($attribute);
        $attributeOption->getCode()->willReturn('option_code');
        $evaluateUpdatedAttributeOptions->evaluate(new AttributeOptionCode(new AttributeCode('attr_code'), 'option_code'))->shouldBeCalled();

        $this->onPostSave(new GenericEvent($attributeOption->getWrappedObject(), ['unitary' => true]));
    }

    public function it_deletes_unknown_attribute_option_on_unitary_attribute_option_post_remove(
        $attributeOptionSpellcheckRepository,
        $dataQualityInsightsFeature,
        AttributeInterface $attribute,
        AttributeOptionInterface $attributeOption
    ): void
    {
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);
        $attribute->getCode()->willReturn('attr_code');
        $attributeOption->getAttribute()->willReturn($attribute);
        $attributeOption->getCode()->willReturn('option_code');
        $attributeOptionSpellcheckRepository->deleteUnknownAttributeOption('attr_code', 'option_code')->shouldBeCalled();

        $this->onPostRemove(new GenericEvent($attributeOption->getWrappedObject(), ['unitary' => true]));
    }
}
