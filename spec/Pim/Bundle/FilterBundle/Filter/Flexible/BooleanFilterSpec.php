<?php

namespace spec\Pim\Bundle\FilterBundle\Filter\Flexible;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Pim\Bundle\FilterBundle\Filter\Flexible\FilterUtility;

class BooleanFilterSpec extends ObjectBehavior
{
    function let(FormFactoryInterface $factory, FilterUtility $utility)
    {
        $this->beConstructedWith($factory, $utility);
        $this->init('foo', [FilterUtility::FEN_KEY => 'baz', FilterUtility::DATA_NAME_KEY => 'bar']);
    }

    function it_is_an_oro_boolean_filter()
    {
        $this->shouldBeAnInstanceOf('Oro\Bundle\FilterBundle\Filter\BooleanFilter');
    }

    function it_initializes_filter_with_name()
    {
        $this->getName()->shouldReturn('foo');
    }

    function it_parses_value_of_type_yes()
    {
        $this->shouldParseData(array('value' => BooleanFilterType::TYPE_YES));
    }

    function it_parses_value_of_type_no()
    {
        $this->shouldParseData(array('value' => BooleanFilterType::TYPE_NO));
    }

    function it_does_not_parse_value_of_other_type()
    {
        $this->shouldNotParseData(array('value' => 'foo'));
    }

    function it_does_not_parse_empty_value()
    {
        $this->shouldNotParseData(array('value' => null));
    }

    function it_does_not_parse_unexisting_value_key()
    {
        $this->shouldNotParseData(array());
    }

    function it_does_not_parse_something_else_than_an_array()
    {
        $this->shouldNotParseData(0);
    }

    function it_applies_boolean_flexible_filter_on_the_datasource(
        FilterDatasourceAdapterInterface $datasource,
        $utility
    ) {
        $utility->applyFlexibleFilter($datasource, 'baz', 'bar', BooleanFilterType::TYPE_YES, '=')->shouldBeCalled();

        $this->apply($datasource, array('value' => BooleanFilterType::TYPE_YES))->shouldReturn(true);
    }

    function it_does_not_apply_boolean_flexible_filter_on_unparsable_data(
        FilterDatasourceAdapterInterface $datasource,
        $utility
    ) {
        $utility->applyFlexibleFilter(Argument::cetera())->shouldNotBeCalled();

        $this->apply($datasource, array('value' => 'foo'))->shouldReturn(false);
        $this->apply($datasource, array('value' => null))->shouldReturn(false);
        $this->apply($datasource, array())->shouldReturn(false);
        $this->apply($datasource, 0)->shouldReturn(false);
    }

    function it_uses_the_boolean_filter_form_type(FormInterface $form, $factory)
    {
        $factory->create(BooleanFilterType::NAME, [], ['csrf_protection' => false])->willReturn($form);

        $this->getForm()->shouldReturn($form);
    }

    function it_generates_choices_metadata(
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
        $factory->create(BooleanFilterType::NAME, [], ['csrf_protection' => false])->willReturn($form);
        $form->createView()->willReturn($formView);

        $formView->children = array('value' => $fieldView, 'type' => $typeView);
        $formView->vars     = array('populate_default' => true);
        $fieldView->vars    = array('multiple' => true, 'choices' => array($yesChoice, $noChoice));
        $typeView->vars     = array('choices' => array($maybeChoice));

        $yesChoice->label = 'Yes';
        $yesChoice->value = 1;
        $noChoice->label  = 'No';
        $noChoice->value  = 0;

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
                'flexible_entity_name' => 'baz',
                'data_name'            => 'bar',
                'contextSearch'        => false,
                'populateDefault'      => true,
                'type'                 => 'multichoice',
            ]
        );

    }

    public function getMatchers()
    {
        return [
            'parseData' => function ($subject, $data) {
                return $subject->parseData($data) === $data;
            },
        ];
    }
}
