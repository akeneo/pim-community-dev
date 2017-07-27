<?php

namespace spec\Pim\Component\Connector\Writer\File;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Repository\AssociationTypeRepositoryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\FieldSplitter;

class ProductColumnSorterSpec extends ObjectBehavior
{
    function let(
        FieldSplitter $fieldSplitter,
        AttributeRepositoryInterface $attributeRepository,
        AssociationTypeRepositoryInterface $associationTypeRepository
    ) {
        $this->beConstructedWith(
            $fieldSplitter,
            $attributeRepository,
            $associationTypeRepository,
            ['label']
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Writer\File\DefaultColumnSorter');
    }

    function it_is_a_sorter()
    {
        $this->shouldImplement('Pim\Component\Connector\Writer\File\ColumnSorterInterface');
    }

    function it_sort_headers_columns($attributeRepository, $fieldSplitter)
    {
        $attributeRepository->getIdentifierCode()->willReturn('sku');

        $fieldSplitter->splitFieldName('sku')->willReturn(['sku']);
        $fieldSplitter->splitFieldName('code')->willReturn(['code']);
        $fieldSplitter->splitFieldName('label')->willReturn(['label']);

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
