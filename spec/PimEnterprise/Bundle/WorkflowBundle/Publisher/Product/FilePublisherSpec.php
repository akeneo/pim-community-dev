<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Publisher\Product;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

class FilePublisherSpec extends ObjectBehavior
{
    function it_is_a_publisher()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Publisher\PublisherInterface');
    }

    function it_supports_file(FileInfoInterface $fileInfo)
    {
        $this->supports($fileInfo)->shouldBe(true);
    }

    function it_publishes_file(
        FileInfoInterface $fileInfoToPublish,
        ProductInterface $product,
        ProductValueInterface $value
    ) {
        $options = ['product' => $product, 'value' => $value];
        $this
            ->publish($fileInfoToPublish, $options)
            ->shouldReturn($fileInfoToPublish);
    }
}
