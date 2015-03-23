<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Processor;

use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductMediaInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\Serializer\Serializer;

class ProductToFlatArrayProcessorSpec extends ObjectBehavior
{
    function let(Serializer $serializer, ChannelManager $channelManager)
    {
        $this->beConstructedWith($serializer, $channelManager, 'upload/path/');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\BaseConnectorBundle\Processor\ProductToFlatArrayProcessor');
    }

    function it_is_an_item_processor()
    {
        $this->shouldImplement('\Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface');
    }

    function it_provides_configuration_fields($channelManager)
    {
        $channelManager->getChannelChoices()->willReturn(['mobile', 'magento']);

        $this->getConfigurationFields()->shouldReturn([
            'channel' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => ['mobile', 'magento'],
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_base_connector.export.channel.label',
                    'help'     => 'pim_base_connector.export.channel.help'
                ]
            ]
        ]);
    }

    function it_is_configurable()
    {
        $this->getChannel()->shouldReturn(null);

        $this->setChannel('mobile');

        $this->getChannel()->shouldReturn('mobile');
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

    function it_throws_an_exception_if_something_goes_wrong_with_media_normalization(
        $serializer,
        ProductInterface $product,
        ProductMediaInterface $media,
        ProductValueInterface $value,
        ProductValueInterface $value2,
        AttributeInterface $attribute
    ) {
        $product->getValues()->willReturn([$value]);
        $product->getIdentifier()->willReturn($value2);

        $value->getAttribute()->willReturn($attribute);
        $value->getData()->willReturn($media);
        $value2->getData()->willReturn(23);

        $attribute->getAttributeType()->willReturn('pim_catalog_image');

        $serializer->normalize([$media], Argument::cetera())->willThrow(new FileNotFoundException('upload/path/img.jpg'));

        $this->shouldThrow(
            new InvalidItemException(
                'The file "upload/path/img.jpg" does not exist',
                [ 'item' => 23, 'uploadDirectory' => 'upload/path/']
            )
        )->duringProcess($product);
    }
}
