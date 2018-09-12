<?php

namespace spec\Oro\Bundle\PimFilterBundle\Filter\ProductValue;

use Oro\Bundle\FilterBundle\Filter\BooleanFilter;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use Oro\Bundle\PimFilterBundle\Form\Type\Filter\BooleanFilterType;
use Prophecy\Argument;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class BooleanFilterSpec extends ObjectBehavior
{
    function let(FormFactoryInterface $factory, ProductFilterUtility $utility)
    {
        $this->beConstructedWith($factory, $utility);
        $this->init('foo', [ProductFilterUtility::DATA_NAME_KEY => 'bar']);
    }

    function it_is_an_oro_boolean_filter()
    {
        $this->shouldBeAnInstanceOf(BooleanFilter::class);
    }

    function it_initializes_filter_with_name()
    {
        $this->getName()->shouldReturn('foo');
    }

    function it_parses_data()
    {
        $this->parseData(['value' => 0])->shouldReturn(['value' => false]);
        $this->parseData(['value' => 1])->shouldReturn(['value' => true]);
        $this->parseData(['value' => true])->shouldReturn(false);
        $this->parseData(['value' => false])->shouldReturn(false);
        $this->parseData(null)->shouldReturn(false);
        $this->parseData([])->shouldReturn(false);
        $this->parseData(1)->shouldReturn(false);
        $this->parseData(0)->shouldReturn(false);
    }

    function it_applies_boolean_flexible_filter_on_the_datasource(
        FilterDatasourceAdapterInterface $datasource,
        $utility
    ) {
        $utility->applyFilter($datasource, 'bar', '=', true)->shouldBeCalled();

        $this->apply($datasource, ['value' => BooleanFilterType::TYPE_YES])->shouldReturn(true);
    }

    function it_does_not_apply_boolean_flexible_filter_on_unparsable_data(
        FilterDatasourceAdapterInterface $datasource,
        $utility
    ) {
        $utility->applyFilter(Argument::cetera())->shouldNotBeCalled();

        $this->apply($datasource, ['value' => 'foo'])->shouldReturn(false);
        $this->apply($datasource, ['value' => null])->shouldReturn(false);
        $this->apply($datasource, [])->shouldReturn(false);
        $this->apply($datasource, BooleanFilterType::TYPE_NO)->shouldReturn(false);
    }

    function it_uses_the_boolean_filter_form_type(FormInterface $form, $factory)
    {
        $factory->create(BooleanFilterType::class, [], ['csrf_protection' => false])->willReturn($form);

        $this->getForm()->shouldReturn($form);
    }

    function it_generates_choices_metadata(
        FormBuilderInterface $formBuilder,
        FormBuilderInterface $typeFormBuilder,
        FormInterface $form,
        FormView $formView,
        FormView $fieldView,
        FormView $typeView,
        ChoiceView $yesChoice,
        ChoiceView $noChoice,
        ChoiceView $maybeChoice,
        $factory,
        $utility
    ) {
        $utility->getParamMap()->willReturn([]);
        $utility->getExcludeParams()->willReturn([]);
        $factory->create(BooleanFilterType::class, [], ['csrf_protection' => false])->willReturn($form);
        $factory->createBuilder(BooleanFilterType::class, [], ['csrf_protection' => false])->willReturn($formBuilder);
        $form->createView()->willReturn($formView);

        $formBuilder->get('type')->willReturn($typeFormBuilder);
        $typeFormBuilder->getOption('choices')->willReturn(['overriden_choice_1' => 0, 'overriden_choice_2' => 1]);
        $formView->children = ['value' => $fieldView, 'type' => $typeView];
        $formView->vars = ['populate_default' => true];
        $fieldView->vars = ['multiple' => true, 'choices' => [$yesChoice, $noChoice]];
        $typeView->vars = ['choices' => [$maybeChoice]];

        $yesChoice->label = 'Yes';
        $yesChoice->value = 1;
        $noChoice->label = 'No';
        $noChoice->value = 0;

        $this->getMetadata()->shouldReturn(
            [
                'name'                 => 'foo',
                'label'                => 'Foo',
                'choices'              => [
                    [
                        'label' => 'Yes',
                        'value' => 1,
                    ],
                    [
                        'label' => 'No',
                        'value' => 0,
                    ]
                ],
                'enabled'              => true,
                'data_name'            => 'bar',
                'populateDefault'      => true,
                'type'                 => 'multichoice',
            ]
        );
    }
}
