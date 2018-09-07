<?php

namespace spec\Oro\Bundle\PimFilterBundle\Filter\ProductValue;

use Oro\Bundle\PimFilterBundle\Filter\ProductValue\AbstractDateFilter;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\DateRangeFilterType;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use Prophecy\Argument;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;

class DateRangeFilterSpec extends ObjectBehavior
{
    function let(FormFactoryInterface $factory, ProductFilterUtility $utility)
    {
        $this->beConstructedWith($factory, $utility);
        $this->init(
            'date_filter',
            [
                ProductFilterUtility::DATA_NAME_KEY => 'data_name_key',
            ]
        );
    }

    function it_is_a_flexible_date_filter()
    {
        $this->shouldBeAnInstanceOf(AbstractDateFilter::class);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('date_filter');
    }

    function it_parses_two_datetime_objects(\DateTime $start, \DateTime $end)
    {
        $start
            ->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))
            ->shouldBeCalled();
        $end
            ->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))
            ->shouldBeCalled();
        $start->format('Y-m-d')->willReturn('1987-05-14');
        $end->format('Y-m-d')->willReturn('2014-01-23');

        $this->parseData([
            'value' => [
                'start' => $start,
                'end'   => $end,
            ],
            'type'  => 1
        ])->shouldReturn([
            'date_start' => '1987-05-14',
            'date_end'   => '2014-01-23',
            'type'       => 1
        ]);
    }

    function it_parses_one_start_date(\DateTime $start)
    {
        $start
            ->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))
            ->shouldBeCalled();
        $start->format('Y-m-d')->willReturn('1987-05-14');

        $this->parseData([
            'value' => [
                'start' => $start,
            ],
            'type'  => 1
        ])->shouldReturn([
            'date_start' => '1987-05-14',
            'date_end'   => null,
            'type'       => 1,
        ]);
    }

    function it_parses_one_end_date(\DateTime $end)
    {
        $end
            ->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))
            ->shouldBeCalled();
        $end->format('Y-m-d')->willReturn('2014-01-23');

        $this->parseData([
            'value' => [
                'end' => $end,
            ],
            'type'  => 1
        ])->shouldReturn([
            'date_start' => null,
            'date_end'   => '2014-01-23',
            'type'       => 1,
        ]);
    }

    function it_parses_between_type_range(\DateTime $start, \DateTime $end)
    {
        $start
            ->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))
            ->shouldBeCalled();
        $start->format('Y-m-d')->willReturn('1987-05-14');
        $end
            ->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))
            ->shouldBeCalled();
        $end->format('Y-m-d')->willReturn('2014-01-23');

        $this->parseData([
            'value' => [
                'start' => $start,
                'end'   => $end,
            ],
            'type'  => DateRangeFilterType::TYPE_BETWEEN,
        ])->shouldReturn([
            'date_start' => '1987-05-14',
            'date_end'   => '2014-01-23',
            'type'       => 1,
        ]);
    }

    function it_parses_not_between_type_range(\DateTime $start, \DateTime $end)
    {
        $start
            ->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))
            ->shouldBeCalled();
        $start->format('Y-m-d')->willReturn('1987-05-14');
        $end
            ->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))
            ->shouldBeCalled();
        $end->format('Y-m-d')->willReturn('2014-01-23');

        $this->parseData([
            'value' => [
                'start' => $start,
                'end'   => $end,
            ],
            'type'  => DateRangeFilterType::TYPE_NOT_BETWEEN,
        ])->shouldReturn([
            'date_start' => '1987-05-14',
            'date_end'   => '2014-01-23',
            'type'       => 2,
        ]);
    }

    function it_parses_more_than_type_range(\DateTime $start, \DateTime $end)
    {
        $start
            ->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))
            ->shouldBeCalled();
        $start->format('Y-m-d')->willReturn('1987-05-14');
        $end
            ->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))
            ->shouldBeCalled();
        $end->format('Y-m-d')->willReturn('2014-01-23');

        $this->parseData([
            'value' => [
                'start' => $start,
                'end'   => $end,
            ],
            'type'  => DateRangeFilterType::TYPE_MORE_THAN,
        ])->shouldReturn([
            'date_start' => '1987-05-14',
            'date_end'   => null,
            'type'       => 3,
        ]);
    }

    function it_parses_less_than_type_range(\DateTime $start, \DateTime $end)
    {
        $start
            ->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))
            ->shouldBeCalled();
        $start->format('Y-m-d')->willReturn('1987-05-14');
        $end
            ->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))
            ->shouldBeCalled();
        $end->format('Y-m-d')->willReturn('2014-01-23');

        $this->parseData([
            'value' => [
                'start' => $start,
                'end'   => $end,
            ],
            'type'  => DateRangeFilterType::TYPE_LESS_THAN,
        ])->shouldReturn([
            'date_start' => null,
            'date_end'   => '2014-01-23',
            'type'       => 4,
        ]);
    }

    function it_fallbacks_on_between_type_range(\DateTime $start, \DateTime $end)
    {
        $start
            ->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))
            ->shouldBeCalled();
        $start->format('Y-m-d')->willReturn('1987-05-14');
        $end
            ->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))
            ->shouldBeCalled();
        $end->format('Y-m-d')->willReturn('2014-01-23');

        $this->parseData([
            'value' => [
                'start' => $start,
                'end'   => $end,
            ],
            'type'  => 'unknown',
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
        $this->parseData(new \StdClass())->shouldReturn(false);
    }

    function it_does_not_parse_array_without_value_key()
    {
        $this->parseData([])->shouldReturn(false);
    }

    function it_does_not_parse_array_without_type_key()
    {
        $this->parseData(['value' => ['start' => '1987-05-14']])->shouldReturn(false);
    }

    function it_does_not_parse_array_without_value_key_of_type_array()
    {
        $this->parseData(['value' => true, 'type' => 1])->shouldReturn(false);
    }

    function it_does_not_parse_array_without_start_and_end_values()
    {
        $this->parseData(['value' => [], 'type' => 1])->shouldReturn(false);
    }

    function it_does_not_parse_array_with_not_datetime_type_start()
    {
        $this->parseData(['value' => ['start' => 'yesterday'], 'type' => 1])->shouldReturn(false);
    }

    function it_does_not_parse_array_with_not_datetime_type_end()
    {
        $this->parseData(['value' => ['end' => 'tomorrow'], 'type' => 1])->shouldReturn(false);
    }

    function it_applies_between_date_range_filter(
        FilterDatasourceAdapterInterface $datasource,
        \DateTime $start,
        \DateTime $end,
        $utility
    ) {
        $start
            ->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))
            ->shouldBeCalled();
        $end
            ->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))
            ->shouldBeCalled();
        $start->format('Y-m-d')->willReturn('1987-05-14');
        $end->format('Y-m-d')->willReturn('2014-01-23');

        $utility
            ->applyFilter($datasource, 'data_name_key', 'BETWEEN', ['1987-05-14', '2014-01-23'])
            ->shouldBeCalled();

        $this->apply(
            $datasource,
            [
                'value' => [
                    'start' => $start,
                    'end'   => $end,
                ],
                'type'  => DateRangeFilterType::TYPE_BETWEEN,
            ]
        );
    }

    function it_applies_not_between_date_range_filter(
        FilterDatasourceAdapterInterface $datasource,
        \DateTime $start,
        \DateTime $end,
        $utility
    ) {
        $start
            ->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))
            ->shouldBeCalled();
        $end
            ->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))
            ->shouldBeCalled();
        $start->format('Y-m-d')->willReturn('1987-05-14');
        $end->format('Y-m-d')->willReturn('2014-01-23');

        $utility
            ->applyFilter(
                $datasource,
                'data_name_key',
                'NOT BETWEEN',
                ['1987-05-14', '2014-01-23']
            )
            ->shouldBeCalled();

        $this->apply(
            $datasource,
            [
                'value' => [
                    'start' => $start,
                    'end'   => $end,
                ],
                'type'  => DateRangeFilterType::TYPE_NOT_BETWEEN,
            ]
        );
    }

    function it_applies_less_than_date_range_filter(
        FilterDatasourceAdapterInterface $datasource,
        \DateTime $start,
        \DateTime $end,
        $utility
    ) {
        $start
            ->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))
            ->shouldBeCalled();
        $end
            ->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))
            ->shouldBeCalled();
        $start->format('Y-m-d')->willReturn('1987-05-14');
        $end->format('Y-m-d')->willReturn('2014-01-23');

        $utility->applyFilter($datasource, 'data_name_key', '<', '2014-01-23')->shouldBeCalled();

        $this->apply(
            $datasource,
            [
                'value' => [
                    'start' => $start,
                    'end'   => $end,
                ],
                'type'  => DateRangeFilterType::TYPE_LESS_THAN,
            ]
        );
    }

    function it_applies_more_than_date_range_filter(
        FilterDatasourceAdapterInterface $datasource,
        \DateTime $start,
        \DateTime $end,
        $utility
    ) {
        $start
            ->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))
            ->shouldBeCalled();
        $end
            ->setTimezone(Argument::allOf(Argument::type('\DateTimeZone'), Argument::which('getName', 'UTC')))
            ->shouldBeCalled();
        $start->format('Y-m-d')->willReturn('1987-05-14');
        $end->format('Y-m-d')->willReturn('2014-01-23');

        $utility->applyFilter($datasource, 'data_name_key', '>', '1987-05-14')->shouldBeCalled();

        $this->apply(
            $datasource,
            [
                'value' => [
                    'start' => $start,
                    'end'   => $end,
                ],
                'type'  => DateRangeFilterType::TYPE_MORE_THAN,
            ]
        );
    }

    function it_provides_date_range_form($factory, Form $form)
    {
        $factory->create(DateRangeFilterType::class, [], ['csrf_protection' => false])->willReturn($form);

        $this->getForm()->shouldReturn($form);
    }
}
