<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Processor;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Doctrine\Common\Collections\ArrayCollection;
use League\Flysystem\Filesystem;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Serializer;

class ProductToFlatArrayProcessorSpec extends ObjectBehavior
{
    function let(Serializer $serializer, ChannelManager $channelManager, ProductBuilderInterface $productBuilder)
    {
        $this->beConstructedWith(
            $serializer,
            $channelManager,
            ['pim_catalog_file', 'pim_catalog_image'],
            ['.', ','],
            [
                ['value' => 'yyyy-MM-dd', 'label' => 'yyyy-mm-dd'],
                ['value' => 'dd.MM.yyyy', 'label' => 'dd.mm.yyyy'],
            ],
            $productBuilder
        );
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

        $this->getConfigurationFields()->shouldReturn(
            [
                'channel' => [
                    'type'    => 'choice',
                    'options' => [
                        'choices'  => ['mobile', 'magento'],
                        'required' => true,
                        'select2'  => true,
                        'label'    => 'pim_base_connector.export.channel.label',
                        'help'     => 'pim_base_connector.export.channel.help'
                    ]
                ],
                'decimalSeparator' => [
                    'type'    => 'choice',
                    'options' => [
                        'choices'  => ['.', ','],
                        'required' => true,
                        'select2'  => true,
                        'label'    => 'pim_base_connector.export.decimalSeparator.label',
                        'help'     => 'pim_base_connector.export.decimalSeparator.help'
                    ]
                ],
                'dateFormat' => [
                    'type'    => 'choice',
                    'options' => [
                        'choices'  => [
                            ['value' => 'yyyy-MM-dd', 'label' => 'yyyy-mm-dd'],
                            ['value' => 'dd.MM.yyyy', 'label' => 'dd.mm.yyyy']
                        ],
                        'required' => true,
                        'select2'  => true,
                        'label'    => 'pim_base_connector.export.dateFormat.label',
                        'help'     => 'pim_base_connector.export.dateFormat.help'
                    ]
                ]
            ]
        );
    }

    function it_is_configurable()
    {
        $this->getChannel()->shouldReturn(null);

        $this->setChannel('mobile');

        $this->getChannel()->shouldReturn('mobile');
    }

    function it_returns_flat_data_with_media(
        $channelManager,
        Filesystem $filesystem,
        ChannelInterface $channel,
        LocaleInterface $locale,
        ProductInterface $product,
        FileInfoInterface $media1,
        FileInfoInterface $media2,
        ProductValueInterface $value1,
        ProductValueInterface $value2,
        AttributeInterface $attribute,
        ProductValueInterface $identifierValue,
        AttributeInterface $identifierAttribute,
        $serializer,
        $productBuilder
    ) {
        $channel->getLocales()->willReturn(new ArrayCollection([$locale]));
        $channel->getLocaleCodes()->willReturn(['en_US']);
        $productBuilder->addMissingProductValues($product, [$channel], [$locale])->shouldBeCalled();

        $media1->getKey()->willReturn('key/to/media1.jpg');
        $media2->getKey()->willReturn('key/to/media2.jpg');

        $value1->getAttribute()->willReturn($attribute);
        $value1->getMedia()->willReturn($media1);
        $value2->getAttribute()->willReturn($attribute);
        $value2->getMedia()->willReturn($media2);
        $attribute->getAttributeType()->willReturn('pim_catalog_image');
        $product->getValues()->willReturn([$value1, $value2, $identifierValue]);

        $identifierValue->getAttribute()->willReturn($identifierAttribute);
        $identifierAttribute->getAttributeType()->willReturn('pim_catalog_identifier');
        $product->getIdentifier()->willReturn($identifierValue);
        $identifierValue->getData()->willReturn('data');

        $filesystem->has('key/to/media1.jpg')->willReturn(true);
        $filesystem->has('key/to/media2.jpg')->willReturn(true);

        $serializer
            ->normalize($media1, 'flat', ['field_name' => 'media', 'prepare_copy' => true, 'value' => $value1])
            ->willReturn(['normalized_media1']);
        $serializer
            ->normalize($media2, 'flat', ['field_name' => 'media', 'prepare_copy' => true, 'value' => $value2])
            ->willReturn(['normalized_media2']);
        $serializer
            ->normalize(
                $product,
                'flat',
                [
                    'scopeCode'         => 'foobar',
                    'localeCodes'       => 'en_US',
                    'decimal_separator' => '.',
                    'date_format'       => 'yyyy-MM-dd',
                ]
            )
            ->willReturn(['normalized_product']);

        $channelManager->getChannelByCode('foobar')->willReturn($channel);

        $this->setChannel('foobar');
        $this->process($product)->shouldReturn(
            [
                'media'   => [['normalized_media1'], ['normalized_media2']],
                'product' => ['normalized_product']
            ]
        );
    }

    function it_returns_flat_data_without_media(
        $productBuilder,
        ChannelInterface $channel,
        LocaleInterface $locale,
        ChannelManager $channelManager,
        ProductInterface $product,
        Serializer $serializer
    ) {
        $channel->getLocales()->willReturn(new ArrayCollection([$locale]));
        $channel->getLocaleCodes()->willReturn(['en_US']);
        $productBuilder->addMissingProductValues($product, [$channel], [$locale])->shouldBeCalled();

        $product->getValues()->willReturn([]);
        $this->setDecimalSeparator(',');

        $serializer
            ->normalize(
                $product,
                'flat',
                [
                    'scopeCode' => 'foobar',
                    'localeCodes' => 'en_US',
                    'decimal_separator' => ',',
                    'date_format'       => 'yyyy-MM-dd',
                ]
            )
            ->willReturn(['normalized_product']);

        $channelManager->getChannelByCode('foobar')->willReturn($channel);

        $this->setChannel('foobar');
        $this->process($product)->shouldReturn(['media' => [], 'product' => ['normalized_product']]);
    }
}
