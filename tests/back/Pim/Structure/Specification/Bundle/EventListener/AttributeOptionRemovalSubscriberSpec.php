<?php

namespace Specification\Akeneo\Pim\Structure\Bundle\EventListener;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductAndProductModelQueryBuilderFactory;
use Akeneo\Pim\Structure\Bundle\EventListener\AttributeOptionRemovalSubscriber;
use Akeneo\Pim\Structure\Component\FamilyVariant\Query\FamilyVariantsByAttributeAxesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class AttributeOptionRemovalSubscriberSpec extends ObjectBehavior
{
    function let(
        FamilyVariantsByAttributeAxesInterface $familyVariantsByAttributeAxes,
        ProductAndProductModelQueryBuilderFactory $pqbFactory
    ) {
        $this->beConstructedWith(
            $familyVariantsByAttributeAxes,
            $pqbFactory
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeOptionRemovalSubscriber::class);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_processes_only_attribute_option(GenericEvent $event, ProductInterface $product)
    {
        $event->getSubject()->willReturn($product);

        $this->disallowRemovalIfOptionIsUsedAsAttributeAxes($event)->shouldReturn(null);
    }

    function it_allows_removal_if_attribute_is_not_used_as_variant_axes(
        $familyVariantsByAttributeAxes,
        $pqbFactory,
        GenericEvent $event,
        AttributeOptionInterface $attributeOption,
        AttributeInterface $attribute
    ) {
        $event->getSubject()->willReturn($attributeOption);

        $attributeOption->getAttribute()->willReturn($attribute);
        $attributeOption->getId()->willReturn(42);

        $attribute->getCode()->willReturn('color');
        $familyVariantsByAttributeAxes->findIdentifiers(['color'])->willReturn([]);

        $pqbFactory->create(Argument::any())->shouldNotBeCalled();

        $this->disallowRemovalIfOptionIsUsedAsAttributeAxes($event)->shouldReturn(null);
    }

    function it_removes_attribute_option_if_it_is_not_used_as_variant_axes(
        $familyVariantsByAttributeAxes,
        $pqbFactory,
        GenericEvent $event,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $cursor,
        AttributeOptionInterface $attributeOption,
        AttributeInterface $attribute
    ) {
        $event->getSubject()->willReturn($attributeOption);

        $attributeOption->getAttribute()->willReturn($attribute);
        $attributeOption->getId()->willReturn(42);
        $attributeOption->getCode()->willReturn('rainbow');

        $attribute->getCode()->willReturn('color');

        $familyVariantsByAttributeAxes
            ->findIdentifiers(['color'])
            ->willReturn(['shoes_by_color', 'millennium_falcon_by_color']);

        $pqbFactory->create([
            'filters' => [
                [
                    'field' => 'family_variant',
                    'operator' => Operators::IN_LIST,
                    'value' => ['shoes_by_color', 'millennium_falcon_by_color'],
                ],
                [
                    'field' => 'color',
                    'operator' => Operators::IN_LIST,
                    'value' => ['rainbow'],
                ]
            ]
        ])->willReturn($pqb);
        $pqb->execute()->willReturn($cursor);
        $cursor->count()->willReturn(0);

        $this->disallowRemovalIfOptionIsUsedAsAttributeAxes($event)->shouldReturn(null);
    }

    function it_does_not_remove_attribute_option_if_it_is_used_as_variant_axes(
        $familyVariantsByAttributeAxes,
        $pqbFactory,
        GenericEvent $event,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $cursor,
        AttributeOptionInterface $attributeOption,
        AttributeInterface $attribute
    ) {
        $event->getSubject()->willReturn($attributeOption);

        $attributeOption->getAttribute()->willReturn($attribute);
        $attributeOption->getId()->willReturn(42);
        $attributeOption->getCode()->willReturn('rainbow');

        $attribute->getCode()->willReturn('color');

        $familyVariantsByAttributeAxes
            ->findIdentifiers(['color'])
            ->willReturn(['shoes_by_color', 'millennium_falcon_by_color']);

        $pqbFactory->create([
            'filters' => [
                [
                    'field' => 'family_variant',
                    'operator' => Operators::IN_LIST,
                    'value' => ['shoes_by_color', 'millennium_falcon_by_color'],
                ],
                [
                    'field' => 'color',
                    'operator' => Operators::IN_LIST,
                    'value' => ['rainbow'],
                ]
            ]
        ])->willReturn($pqb);
        $pqb->execute()->willReturn($cursor);
        $cursor->count()->willReturn(1308);

        $this
            ->shouldThrow(
                new \LogicException('Attribute option "rainbow" could not be removed as it is used as variant axis value.')
            )
            ->during(
                'disallowRemovalIfOptionIsUsedAsAttributeAxes',
                [$event]
            );
    }
}
