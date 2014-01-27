<?php

namespace spec\Pim\Bundle\FilterBundle\Filter\Flexible;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactoryInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\DateRangeFilterType;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Pim\Bundle\FilterBundle\Filter\Flexible\FilterUtility;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormView;

class DateRangeFilterSpec extends ObjectBehavior
{
    function let(FormFactoryInterface $factory, FilterUtility $utility)
    {
        $this->beConstructedWith($factory, $utility);
        $this->init(
            'date_filter',
            [
                FilterUtility::FEN_KEY       => 'fen_key',
                FilterUtility::DATA_NAME_KEY => 'data_name_key',
            ]
        );
    }

    function it_is_a_flexible_date_filter()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\FilterBundle\Filter\Flexible\AbstractFlexibleDateFilter');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('date_filter');
    }

    function it_parses_two_datetime_objects(\DateTime $start, \DateTime $end)
    {
        $start->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))->shouldBeCalled();
        $end->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))->shouldBeCalled();
        $start->format('Y-m-d')->willReturn('1987-05-14');
        $end->format('Y-m-d')->willReturn('2014-01-23');

        $this->parseData([
            'value' => [
                'start' => $start,
                'end'   => $end,
            ]
        ])->shouldReturn([
            'date_start' => '1987-05-14',
            'date_end'   => '2014-01-23',
            'type'       => 1,
        ]);
    }

    function it_parses_one_start_date(\DateTime $start)
    {
        $start->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))->shouldBeCalled();
        $start->format('Y-m-d')->willReturn('1987-05-14');

        $this->parseData([
            'value' => [
                'start' => $start,
            ]
        ])->shouldReturn([
            'date_start' => '1987-05-14',
            'date_end'   => null,
            'type'       => 1,
        ]);
    }

    function it_parses_one_end_date(\DateTime $end)
    {
        $end->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))->shouldBeCalled();
        $end->format('Y-m-d')->willReturn('2014-01-23');

        $this->parseData([
            'value' => [
                'end'   => $end,
            ]
        ])->shouldReturn([
            'date_start' => null,
            'date_end'   => '2014-01-23',
            'type'       => 1,
        ]);
    }

    function it_parses_between_type_range(\DateTime $start, \DateTime $end)
    {
        $start->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))->shouldBeCalled();
        $start->format('Y-m-d')->willReturn('1987-05-14');
        $end->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))->shouldBeCalled();
        $end->format('Y-m-d')->willReturn('2014-01-23');

        $this->parseData([
            'value' => [
                'start' => $start,
                'end'   => $end,
            ],
            'type' => DateRangeFilterType::TYPE_BETWEEN,
        ])->shouldReturn([
            'date_start' => '1987-05-14',
            'date_end'   => '2014-01-23',
            'type'       => 1,
        ]);
    }

    function it_parses_not_between_type_range(\DateTime $start, \DateTime $end)
    {
        $start->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))->shouldBeCalled();
        $start->format('Y-m-d')->willReturn('1987-05-14');
        $end->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))->shouldBeCalled();
        $end->format('Y-m-d')->willReturn('2014-01-23');

        $this->parseData([
            'value' => [
                'start' => $start,
                'end'   => $end,
            ],
            'type' => DateRangeFilterType::TYPE_NOT_BETWEEN,
        ])->shouldReturn([
            'date_start' => '1987-05-14',
            'date_end'   => '2014-01-23',
            'type'       => 2,
        ]);
    }

    function it_parses_more_than_type_range(\DateTime $start, \DateTime $end)
    {
        $start->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))->shouldBeCalled();
        $start->format('Y-m-d')->willReturn('1987-05-14');
        $end->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))->shouldBeCalled();
        $end->format('Y-m-d')->willReturn('2014-01-23');

        $this->parseData([
            'value' => [
                'start' => $start,
                'end'   => $end,
            ],
            'type' => DateRangeFilterType::TYPE_MORE_THAN,
        ])->shouldReturn([
            'date_start' => '1987-05-14',
            'date_end'   => null,
            'type'       => 3,
        ]);
    }

    function it_parses_less_than_type_range(\DateTime $start, \DateTime $end)
    {
        $start->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))->shouldBeCalled();
        $start->format('Y-m-d')->willReturn('1987-05-14');
        $end->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))->shouldBeCalled();
        $end->format('Y-m-d')->willReturn('2014-01-23');

        $this->parseData([
            'value' => [
                'start' => $start,
                'end'   => $end,
            ],
            'type' => DateRangeFilterType::TYPE_LESS_THAN,
        ])->shouldReturn([
            'date_start' => null,
            'date_end'   => '2014-01-23',
            'type'       => 4,
        ]);
    }

    function it_fallbacks_on_between_type_range(\DateTime $start, \DateTime $end)
    {
        $start->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))->shouldBeCalled();
        $start->format('Y-m-d')->willReturn('1987-05-14');
        $end->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))->shouldBeCalled();
        $end->format('Y-m-d')->willReturn('2014-01-23');

        $this->parseData([
            'value' => [
                'start' => $start,
                'end'   => $end,
            ],
            'type' => 'unknown',
        ])->shouldReturn([
            'date_start' => '1987-05-14',
            'date_end'   => '2014-01-23',
            'type'       => 1,
        ]);
    }

    function it_does_not_parse_something_else_than_an_array()
    {
        $this->parseData('foo')->shouldReturn(false);
        $this->parseData(0)->shouldReturn(false);
        $this->parseData(true)->shouldReturn(false);
        $this->parseData(new \StdClass)->shouldReturn(false);
    }

    function it_does_not_parse_array_without_value_key()
    {
        $this->parseData([])->shouldReturn(false);
    }

    function it_does_not_parse_array_without_value_key_of_type_array()
    {
        $this->parseData(['value' => true])->shouldReturn(false);
    }

    function it_does_not_parse_array_without_start_and_end_values()
    {
        $this->parseData(['value' => []])->shouldReturn(false);
    }

    function it_does_not_parse_array_with_not_datetime_type_start()
    {
        $this->parseData(['value' => ['start' => 'yesterday']])->shouldReturn(false);
    }

    function it_does_not_parse_array_with_not_datetime_type_end()
    {
        $this->parseData(['value' => ['end' => 'tomorrow']])->shouldReturn(false);
    }

    function it_applies_between_date_range_filter(
        FilterDatasourceAdapterInterface $datasource,
        \DateTime $start,
        \DateTime $end,
        $utility
    ) {
        $start->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))->shouldBeCalled();
        $end->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))->shouldBeCalled();
        $start->format('Y-m-d')->willReturn('1987-05-14');
        $end->format('Y-m-d')->willReturn('2014-01-23');

        $utility->applyFlexibleFilter($datasource, 'fen_key', 'data_name_key', array('1987-05-14', '2014-01-23'), 'BETWEEN')->shouldBeCalled();

        $this->apply(
            $datasource,
            [
                'value' => [
                    'start' => $start,
                    'end'   => $end,
                ],
                'type' => DateRangeFilterType::TYPE_BETWEEN,
            ]
        );
    }

    function it_applies_not_between_date_range_filter(
        FilterDatasourceAdapterInterface $datasource,
        \DateTime $start,
        \DateTime $end,
        $utility
    ) {
        $start->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))->shouldBeCalled();
        $end->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))->shouldBeCalled();
        $start->format('Y-m-d')->willReturn('1987-05-14');
        $end->format('Y-m-d')->willReturn('2014-01-23');

        $utility->applyFlexibleFilter($datasource, 'fen_key', 'data_name_key', ['from' => '1987-05-14', 'to' => '2014-01-23'], ['from' => '<', 'to' => '>'])->shouldBeCalled();

        $this->apply(
            $datasource,
            [
                'value' => [
                    'start' => $start,
                    'end'   => $end,
                ],
                'type' => DateRangeFilterType::TYPE_NOT_BETWEEN,
            ]
        );
    }

    function it_applies_less_than_date_range_filter(
        FilterDatasourceAdapterInterface $datasource,
        \DateTime $start,
        \DateTime $end,
        $utility
    ) {
        $start->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))->shouldBeCalled();
        $end->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))->shouldBeCalled();
        $start->format('Y-m-d')->willReturn('1987-05-14');
        $end->format('Y-m-d')->willReturn('2014-01-23');

        $utility->applyFlexibleFilter($datasource, 'fen_key', 'data_name_key', '2014-01-23', '<')->shouldBeCalled();

        $this->apply(
            $datasource,
            [
                'value' => [
                    'start' => $start,
                    'end'   => $end,
                ],
                'type' => DateRangeFilterType::TYPE_LESS_THAN,
            ]
        );
    }

    function it_applies_more_than_date_range_filter(
        FilterDatasourceAdapterInterface $datasource,
        \DateTime $start,
        \DateTime $end,
        $utility
    ) {
        $start->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))->shouldBeCalled();
        $end->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))->shouldBeCalled();
        $start->format('Y-m-d')->willReturn('1987-05-14');
        $end->format('Y-m-d')->willReturn('2014-01-23');

        $utility->applyFlexibleFilter($datasource, 'fen_key', 'data_name_key', '1987-05-14', '>')->shouldBeCalled();

        $this->apply(
            $datasource,
            [
                'value' => [
                    'start' => $start,
                    'end'   => $end,
                ],
                'type' => DateRangeFilterType::TYPE_MORE_THAN,
            ]
        );
    }

    function it_provides_date_range_form($factory, Form $form)
    {
        $factory->create(DateRangeFilterType::NAME, [], ['csrf_protection' => false])->willReturn($form);

        $this->getForm()->shouldReturn($form);
    }

    function it_provides_metadata($factory, $utility, Form $form, FormView $formView, FormView $typeView)
    {
        $factory->create(DateRangeFilterType::NAME, [], ['csrf_protection' => false])->willReturn($form);
        $form->createView()->willReturn($formView);
        $formView->vars = [
            'type_values'    => ['foo', 'bar'],
            'widget_options' => ['boo' => 'far'],
        ];
        $formView->children = ['type' => $typeView];
        $typeView->vars     = ['choices' => ['a', 'b', 'c']];

        $utility->getParamMap()->willReturn([]);
        $utility->getExcludeParams()->willReturn([]);

        $this->getMetadata()->shouldReturn([
            'name'                  => 'date_filter',
            'label'                 => 'Date_filter',
            'choices'               => ['a', 'b', 'c'],
            'enabled'               => true,
            'flexible_entity_name'  => 'fen_key',
            'data_name'             => 'data_name_key',
            'typeValues'            => ['foo', 'bar'],
            'externalWidgetOptions' => ['boo' => 'far'],
        ]);
    }
}
