<?php

namespace spec\Pim\Bundle\FilterBundle\Filter\Flexible;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactoryInterface;
use Doctrine\Common\Collections\Collection;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceFilterType;
use Pim\Bundle\FilterBundle\Filter\Flexible\FilterUtility;
use Symfony\Component\Form\Form;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeOptionRepository;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;

class ChoiceFilterSpec extends ObjectBehavior
{
    function let(FormFactoryInterface $factory, FilterUtility $utility)
    {
        $this->beConstructedWith($factory, $utility);

        $this->init(
            'foo',
            [
                FilterUtility::FEN_KEY       => 'fen_key',
                FilterUtility::DATA_NAME_KEY => 'data_name_key',
            ]
        );
    }

    function it_is_an_oro_choice_filter()
    {
        $this->shouldBeAnInstanceOf('Oro\Bundle\FilterBundle\Filter\ChoiceFilter');
    }

    function it_initializes_filter_with_name()
    {
        $this->getName()->shouldReturn('foo');
    }

    function it_applies_choice_filter_on_datasource_for_array_value(
        FilterDatasourceAdapterInterface $datasource,
        $utility
    ) {
        $utility->applyFlexibleFilter($datasource, 'fen_key', 'data_name_key', ['foo', 'bar'], 'IN')->shouldBeCalled();

        $this->apply(
            $datasource,
            [
                'value' => ['foo', 'bar'],
                'type'  => ChoiceFilterType::TYPE_CONTAINS,
            ]
        );
    }

    function it_applies_choice_filter_on_datasource_for_collection_value(
        FilterDatasourceAdapterInterface $datasource,
        Collection $collection,
        $utility
    ) {
        $collection->count()->willReturn(2);
        $collection->getValues()->willReturn(['foo', 'bar']);
        $utility->applyFlexibleFilter($datasource, 'fen_key', 'data_name_key', ['foo', 'bar'], 'IN')->shouldBeCalled();

        $this->apply(
            $datasource,
            [
                'value' => $collection,
                'type'  => ChoiceFilterType::TYPE_CONTAINS,
            ]
        );
    }

    function it_applies_choice_filter_on_datasource_for_array_value_with_not_contains_type(
        FilterDatasourceAdapterInterface $datasource,
        $utility
    ) {
        $utility->applyFlexibleFilter($datasource, 'fen_key', 'data_name_key', ['foo', 'bar'], 'NOT IN')->shouldBeCalled();

        $this->apply(
            $datasource,
            [
                'value' => ['foo', 'bar'],
                'type'  => ChoiceFilterType::TYPE_NOT_CONTAINS,
            ]
        );
    }

    function it_falbacks_on_contains_type_if_type_is_unknown(
        FilterDatasourceAdapterInterface $datasource,
        $utility
    ) {
        $utility->applyFlexibleFilter($datasource, 'fen_key', 'data_name_key', ['foo', 'bar'], 'IN')->shouldBeCalled();

        $this->apply(
            $datasource,
            [
                'value' => ['foo', 'bar'],
                'type'  => 'unknown',
            ]
        );
    }

    /**
     * TODO The getForm method is obviously too smart here
     */
    function it_provides_a_choice_filter_form(
        Form $form,
        FlexibleManager $flexibleManager,
        AttributeRepository $attributeRepository,
        AttributeOptionRepository $attributeOptionRepository,
        Attribute $attribute,
        AttributeOption $optionAlpha,
        AttributeOption $optionBeta,
        AttributeOptionValue $optionValueAlpha,
        $utility,
        $factory
    ) {
        $utility->getFlexibleManager('fen_key')->willReturn($flexibleManager);
        $flexibleManager->getAttributeRepository()->willReturn($attributeRepository);
        $flexibleManager->getAttributeOptionRepository()->willReturn($attributeOptionRepository);
        $flexibleManager->getFlexibleName()->willReturn('foo');

        $attributeRepository->findOneBy(['entityType' => 'foo', 'code' => 'data_name_key'])->willReturn($attribute);
        $attributeOptionRepository->findAllForAttributeWithValues($attribute)->willReturn([$optionAlpha, $optionBeta]);

        $optionAlpha->getOptionValue()->willReturn($optionValueAlpha);
        $optionBeta->getOptionValue()->willReturn(null);
        $optionAlpha->getId()->willReturn(1);
        $optionValueAlpha->getValue()->willReturn('alpha');
        $optionBeta->getId()->willReturn(2);
        $optionBeta->getCode()->willReturn('beta');

        $factory->create(ChoiceFilterType::NAME, [], [
            'csrf_protection' => false,
            'field_options' => [
                'choices' => [
                    1 => 'alpha',
                    2 => '[beta]'
                ]
            ]
        ])->willReturn($form);

        $this->getForm()->shouldReturn($form);
    }
}
