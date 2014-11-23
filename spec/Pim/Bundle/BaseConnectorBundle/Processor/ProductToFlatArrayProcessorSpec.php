<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Processor;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\CatalogBundle\Model\ProductMediaInterface;
use Symfony\Component\Serializer\Serializer;

class ProductToFlatArrayProcessorSpec extends ObjectBehavior
{
    function let(Serializer $serializer, ChannelManager $channelManager)
    {
        $this->beConstructedWith($serializer, $channelManager);
    }

    function it_returns_flat_data_with_media(
        Channel $channel,
        $channelManager,
        AbstractProduct $item,
        ProductMediaInterface $media1,
        ProductMediaInterface $media2,
        $serializer
    ) {
        $media1->getFilename()->willReturn('media_name');
        $media1->getFilePath()->willReturn('media_file_path');
        $media1->getOriginalFilename()->willReturn('media_original_name');

        $media2->getFilename()->willReturn('media_name');
        $media2->getFilePath()->willReturn('media_file_path');
        $media2->getOriginalFilename()->willReturn('media_original_name');

        $item->getMedia()->willReturn([$media1, $media2]);

        $serializer
            ->normalize([$media1, $media2], 'flat', ['field_name' => 'media', 'prepare_copy' => true])
            ->willReturn(['normalized_media1', 'normalized_media2']);

        $serializer
            ->normalize($item, 'flat', ['scopeCode' => 'foobar', 'localeCodes' => ''])
            ->willReturn(['normalized_product']);

        $channelManager->getChannelByCode('foobar')->willReturn($channel);

        $this->setChannel('foobar');
        $this->process($item)->shouldReturn(['media' => ['normalized_media1', 'normalized_media2'], 'product' => ['normalized_product']]);
    }

    function it_returns_flat_data_without_media(
        Channel $channel,
        ChannelManager $channelManager,
        AbstractProduct $item,
        Serializer $serializer
    ) {
        $item->getMedia()->willReturn([]);

        $serializer
            ->normalize($item, 'flat', ['scopeCode' => 'foobar', 'localeCodes' => ''])
            ->willReturn(['normalized_product']);

        $channelManager->getChannelByCode('foobar')->willReturn($channel);

        $this->setChannel('foobar');
        $this->process($item)->shouldReturn(['media' => [], 'product' => ['normalized_product']]);
    }

}
