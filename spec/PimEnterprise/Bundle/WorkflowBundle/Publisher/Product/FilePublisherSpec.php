<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Publisher\Product;

use Akeneo\Component\FileStorage\Model\FileInterface;
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

    function it_supports_file(FileInterface $file)
    {
        $this->supports($file)->shouldBe(true);
    }

    function it_publishes_file(
        FileInterface $fileToPublish,
        ProductInterface $product,
        ProductValueInterface $value
    ) {
        $options = ['product' => $product, 'value' => $value];
        $this
            ->publish($fileToPublish, $options)
            ->shouldReturn($fileToPublish);
    }
}
