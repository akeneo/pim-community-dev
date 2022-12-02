<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\FieldSplitter;
use Akeneo\Pim\Structure\Component\Model\AssociationType;
use Akeneo\Pim\Structure\Component\Repository\AssociationTypeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Connector\Writer\File\ColumnSorterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\DefaultColumnSorter;
use PhpSpec\ObjectBehavior;

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

    function it_sort_headers_columns(
        AttributeRepositoryInterface $attributeRepository,
        FieldSplitter $fieldSplitter,
        AssociationTypeRepositoryInterface $associationTypeRepository
    ) {
        $attributeRepository->getIdentifierCode()->willReturn('sku');
        $associationTypeRepository->findAll()->willReturn([]);

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

    function it_sorts_association_columns(
        AttributeRepositoryInterface $attributeRepository,
        FieldSplitter $fieldSplitter,
        AssociationTypeRepositoryInterface $associationTypeRepository
    ) {
        $attributeRepository->getIdentifierCode()->willReturn('sku');
        $associationType = new AssociationType();
        $associationType->setCode('my_association');
        $associationTypeRepository->findAll()->willReturn([$associationType]);

        $fieldSplitter->splitFieldName('sku')->willReturn(['sku']);
        $fieldSplitter->splitFieldName('code')->willReturn(['code']);
        $fieldSplitter->splitFieldName('label')->willReturn(['label']);
        $fieldSplitter->splitFieldName('my_association-product_uuids')->willReturn(['my_association']);
        $fieldSplitter->splitFieldName('my_association-products-quantity')->willReturn(['my_association']);

        $this->sort([
            'label',
            'my_association-product_uuids',
            'my_association-products-quantity',
            'code',
            'sku',
        ])->shouldReturn([
            'sku',
            'label',
            'my_association-product_uuids',
            'my_association-products-quantity',
            'code',
        ]);
    }
}
