<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Processor;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Model\Product;
use Pim\Bundle\TransformBundle\Normalizer\FlatProductNormalizer;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Model\Media;

class ProductToFlatArrayProcessorSpec extends ObjectBehavior
{
    function let(
        FlatProductNormalizer $flatProductNormalizer,
        ChannelManager $channelManager
    ) {
        $this->beConstructedWith($flatProductNormalizer, $channelManager);
    }

    function it_return_flat_data(
        Channel $channel,
        ChannelManager $channelManager,
        Product $item,
        Media $media1,
        Media $media2,
        FlatProductNormalizer $flatProductNormalizer
    ) {
        $media1->getFilename()->willReturn('media_name');
        $media1->getFilePath()->willReturn('media_file_path');
        $media1->getOriginalFilename()->willReturn('media_original_name');

        $media2->getFilename()->willReturn('media_name');
        $media2->getFilePath()->willReturn('media_file_path');
        $media2->getOriginalFilename()->willReturn('media_original_name');

        $item->getMedia()->willReturn([$media1, $media2]);

        $flatProductNormalizer
            ->normalize($item, null, ['scopeCode' => 'foobar', 'localeCodes' => ''])
            ->willReturn(['normalized_product']);

        $channelManager->getChannelByCode('foobar')->willReturn($channel);

        $this->setChannel('foobar');
        $this->process($item)->shouldReturn(['media' => [$media1, $media2], 'product' => ['normalized_product']]);
    }
}
