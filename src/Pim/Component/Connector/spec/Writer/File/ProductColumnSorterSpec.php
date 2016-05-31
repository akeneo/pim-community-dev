<?php

namespace spec\Pim\Component\Connector\Writer\File;

use PhpSpec\ObjectBehavior;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\FieldSplitter;

class ProductColumnSorterSpec extends ObjectBehavior
{
    function let(FieldSplitter $fieldSplitter, IdentifiableObjectRepositoryInterface $productRepository)
    {
        $this->beConstructedWith($fieldSplitter, $productRepository, ['label']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Writer\File\DefaultColumnSorter');
    }

    function it_is_a_sorter()
    {
        $this->shouldImplement('Pim\Component\Connector\Writer\File\ColumnSorterInterface');
    }

    function it_sort_headers_columns($productRepository)
    {
        $productRepository->getIdentifierProperties()->willReturn([0 => 'sku']);

        $this->sort([
            'sku',
            'code',
            'label'
        ])->shouldReturn([
            'sku',
            'label',
            'code'
        ]);
    }
}
