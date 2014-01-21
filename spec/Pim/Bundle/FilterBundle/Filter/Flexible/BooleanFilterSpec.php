<?php

namespace spec\Pim\Bundle\FilterBundle\Filter\Flexible;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactoryInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;
use Pim\Bundle\FilterBundle\Filter\Flexible\FilterUtility;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;

class BooleanFilterSpec extends ObjectBehavior
{
    function let(FormFactoryInterface $factory, FilterUtility $utility)
    {
        $this->beConstructedWith($factory, $utility);
        $this->init('foo', [FilterUtility::FEN_KEY => 'baz', FilterUtility::DATA_NAME_KEY => 'bar']);
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

    public function getMatchers()
    {
        return [
            'parseData' => function ($subject, $data) {
                return $subject->parseData($data) === $data;
            },
        ];
    }
}
