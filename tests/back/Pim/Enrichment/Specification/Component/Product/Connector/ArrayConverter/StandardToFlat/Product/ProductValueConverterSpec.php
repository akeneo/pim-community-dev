<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product;

use Akeneo\Tool\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter\AbstractValueConverter;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter\ValueConverterRegistry;

class ProductValueConverterSpec extends ObjectBehavior
{
    function let(
        ValueConverterRegistry $converterRegistry,
        CachedObjectRepositoryInterface $attributeRepo
    ) {
        $this->beConstructedWith($converterRegistry, $attributeRepo);
    }

    function it_converts_product_value_attributes_from_standard_to_flat_format(
        $converterRegistry,
        $attributeRepo,
        AttributeInterface $attribute,
        AbstractValueConverter $arrayConverter
    ) {
        $attributeRepo->findOneByIdentifier('description')->willReturn($attribute);
        $attribute->getType()->willReturn('pim_catalog_textarea');
        $converterRegistry->getConverter($attribute)->willReturn($arrayConverter);

        $data = [
            [
                'locale' => 'fr_FR',
                'scope'  => null,
                'data'   => 'T-Rex en plastique.'
            ]
        ];

        $converterResult = [
            'description-fr_FR' => 'T-Rex en plastique.'
        ];

        $arrayConverter->convert('description', $data)->shouldBeCalled()->willReturn($converterResult);

        $this->convertAttribute('description', $data)->shouldReturn($converterResult);

    }

    function it_throws_a_logic_exception_if_no_converter_available(
        $converterRegistry,
        $attributeRepo,
        AttributeInterface $attribute
    ) {
        $attributeRepo->findOneByIdentifier('weight')->willReturn($attribute);
        $attribute->getType()->willReturn('pim_catalog_metric');
        $converterRegistry->getConverter($attribute)->willReturn(null);

        $this->shouldThrow(
            new \LogicException('No standard to flat array converter found for attribute type "pim_catalog_metric"')
        )->during('convertAttribute', ['weight', [], []]);
    }
}
