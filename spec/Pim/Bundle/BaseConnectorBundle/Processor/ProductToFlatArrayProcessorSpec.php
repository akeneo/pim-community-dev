<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Processor;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductMediaInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\Serializer\Serializer;

class ProductToFlatArrayProcessorSpec extends ObjectBehavior
{
    function let(Serializer $serializer, ChannelManager $channelManager)
    {
        $this->beConstructedWith($serializer, $channelManager, 'upload/path/');
    }

    function it_returns_flat_data_with_media(
        ChannelInterface $channel,
        $channelManager,
        ProductInterface $product,
        ProductMediaInterface $media1,
        ProductMediaInterface $media2,
        ProductValueInterface $value1,
        ProductValueInterface $value2,
        AttributeInterface $attribute,
        $serializer
    ) {
        $media1->getFilename()->willReturn('media_name');
        $media1->getOriginalFilename()->willReturn('media_original_name');

        $media2->getFilename()->willReturn('media_name');
        $media2->getOriginalFilename()->willReturn('media_original_name');

        $value1->getAttribute()->willReturn($attribute);
        $value1->getData()->willReturn($media1);
        $value2->getAttribute()->willReturn($attribute);
        $value2->getData()->willReturn($media2);
        $attribute->getAttributeType()->willReturn('pim_catalog_image');
        $product->getValues()->willReturn([$value1, $value2]);

        $serializer
            ->normalize([$media1, $media2], 'flat', ['field_name' => 'media', 'prepare_copy' => true])
            ->willReturn(['normalized_media1', 'normalized_media2']);

        $serializer
            ->normalize($product, 'flat', ['scopeCode' => 'foobar', 'localeCodes' => ''])
            ->willReturn(['normalized_product']);

        $channelManager->getChannelByCode('foobar')->willReturn($channel);

        $this->setChannel('foobar');
        $this->process($product)->shouldReturn(['media' => ['normalized_media1', 'normalized_media2'], 'product' => ['normalized_product']]);
    }

    function it_returns_flat_data_without_media(
        ChannelInterface $channel,
        ChannelManager $channelManager,
        ProductInterface $product,
        Serializer $serializer
    ) {
        $product->getValues()->willReturn([]);

        $serializer
            ->normalize($product, 'flat', ['scopeCode' => 'foobar', 'localeCodes' => ''])
            ->willReturn(['normalized_product']);

        $channelManager->getChannelByCode('foobar')->willReturn($channel);

        $this->setChannel('foobar');
        $this->process($product)->shouldReturn(['media' => [], 'product' => ['normalized_product']]);
    }

    function it_throws_an_exception_if_no_file_is_found(
        ChannelInterface $channel,
        ProductInterface $product,
        ChannelManager $channelManager,
        Serializer $serializer,
        ProductValueInterface $productValue,
        AttributeInterface $attribute
    ) {
        $product->getValues()->willReturn([$productValue]);
        $productValue->getAttribute()->willReturn($attribute);
        $attribute->getAttributeType()->willReturn('pim_catalog_image');
        $product->getIdentifier()->willReturn($productValue);
        $productValue->getData()->willReturn('data');
        $this->setChannel('foobar');
        $channelManager->getChannelByCode('foobar')->willReturn($channel);

        $serializer
            ->normalize(['data'], 'flat', ['field_name' => 'media', 'prepare_copy' => true])
            ->willThrow('Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException');

        $this->shouldThrow('Akeneo\Bundle\BatchBundle\Item\InvalidItemException')->during('process', [$product]);
    }

}
