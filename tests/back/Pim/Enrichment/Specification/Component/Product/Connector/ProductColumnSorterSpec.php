<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector;

use Akeneo\Tool\Component\Connector\Writer\File\DefaultColumnSorter;
use Akeneo\Tool\Component\Connector\Writer\File\ColumnSorterInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Repository\AssociationTypeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\FieldSplitter;

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
        $this->shouldHaveType(DefaultColumnSorter::class);
    }

    function it_is_a_sorter()
    {
        $this->shouldImplement(ColumnSorterInterface::class);
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
