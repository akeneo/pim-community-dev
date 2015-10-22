<?php

namespace spec\Pim\Component\Connector\Writer\File;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Prophecy\Argument;

class MediaExporterPathGeneratorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Writer\File\MediaExporterPathGenerator');
    }

    function it_throws_an_exception_when_the_provided_object_is_not_product_value()
    {
        $this->shouldThrow('\InvalidArgumentException')->during('generate', [new \StdClass()]);
    }

    function it_generates_the_path_when_there_is_no_media(ProductValueInterface $value)
    {
        $value->getMedia()->willReturn(null);

        $this->generate($value)->shouldReturn('');
    }

    function it_generates_the_path(
        ProductValueInterface $value,
        FileInfoInterface $fileInfo,
        AttributeInterface $attribute
    ) {
        $value->getMedia()->willReturn($fileInfo);
        $value->getAttribute()->willReturn($attribute);
        $fileInfo->getOriginalFilename()->willReturn('file.jpg');
        $attribute->getCode()->willReturn('picture');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);

        $this->generate($value, ['identifier' => 'sku001'])->shouldReturn('files/sku001/picture/file.jpg');
    }

    function it_generates_the_path_when_no_identifier_is_provided(
        ProductValueInterface $value,
        ProductInterface $product,
        FileInfoInterface $fileInfo,
        AttributeInterface $attribute
    ) {
        $value->getMedia()->willReturn($fileInfo);
        $value->getAttribute()->willReturn($attribute);
        $value->getEntity()->willReturn($product);
        $product->getIdentifier()->willReturn('sku-product');
        $fileInfo->getOriginalFilename()->willReturn('file.jpg');
        $attribute->getCode()->willReturn('picture');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);

        $this->generate($value, ['identifier' =>null])->shouldReturn('files/sku-product/picture/file.jpg');
    }

    function it_generates_the_path_when_the_value_is_localisable(
        ProductValueInterface $value,
        FileInfoInterface $fileInfo,
        AttributeInterface $attribute
    ) {
        $value->getMedia()->willReturn($fileInfo);
        $value->getLocale()->willReturn('fr_FR');
        $value->getAttribute()->willReturn($attribute);
        $fileInfo->getOriginalFilename()->willReturn('file.jpg');
        $attribute->getCode()->willReturn('picture');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(false);

        $this->generate($value, ['identifier' => 'sku001'])->shouldReturn('files/sku001/picture/fr_FR/file.jpg');
    }

    function it_generates_the_path_when_the_value_is_scopable(
        ProductValueInterface $value,
        FileInfoInterface $fileInfo,
        AttributeInterface $attribute
    ) {
        $value->getMedia()->willReturn($fileInfo);
        $value->getScope()->willReturn('ecommerce');
        $value->getAttribute()->willReturn($attribute);
        $fileInfo->getOriginalFilename()->willReturn('file.jpg');
        $attribute->getCode()->willReturn('picture');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(true);

        $this->generate($value, ['identifier' => 'sku001'])->shouldReturn('files/sku001/picture/ecommerce/file.jpg');
    }

    function it_generates_the_path_when_the_value_is_localisable_and_scopable(
        ProductValueInterface $value,
        FileInfoInterface $fileInfo,
        AttributeInterface $attribute
    ) {
        $value->getMedia()->willReturn($fileInfo);
        $value->getLocale()->willReturn('fr_FR');
        $value->getScope()->willReturn('ecommerce');
        $value->getAttribute()->willReturn($attribute);
        $fileInfo->getOriginalFilename()->willReturn('file.jpg');
        $attribute->getCode()->willReturn('picture');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(true);

        $this->generate($value, ['identifier' => 'sku001'])->shouldReturn('files/sku001/picture/fr_FR/ecommerce/file.jpg');
    }
}
