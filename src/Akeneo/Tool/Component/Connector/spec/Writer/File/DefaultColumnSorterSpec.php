<?php

namespace spec\Akeneo\Tool\Component\Connector\Writer\File;

use Akeneo\Tool\Component\Connector\Writer\File\DefaultColumnSorter;
use Akeneo\Tool\Component\Connector\Writer\File\ColumnSorterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldSplitter;

class DefaultColumnSorterSpec extends ObjectBehavior
{
    function let(FieldSplitter $fieldSplitter)
    {
        $this->beConstructedWith($fieldSplitter, ['code', 'label']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DefaultColumnSorter::class);
    }

    function it_is_a_sorter()
    {
        $this->shouldImplement(ColumnSorterInterface::class);
    }

    function it_sort_headers_columns($fieldSplitter)
    {
        $fieldSplitter->splitFieldName('code')->willReturn(['code']);
        $fieldSplitter->splitFieldName('sort_order')->willReturn(['sort_order']);
        $fieldSplitter->splitFieldName('label')->willReturn(['label']);

        $this->sort([
            'code',
            'sort_order',
            'label',
        ])->shouldReturn([
            'code',
            'label',
            'sort_order'
        ]);
    }
}
