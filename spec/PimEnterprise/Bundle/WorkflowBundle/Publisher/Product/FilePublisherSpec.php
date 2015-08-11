<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Publisher\Product;

use Akeneo\Component\FileStorage\Model\FileInterface;
use Akeneo\Component\FileStorage\RawFile\RawFileFetcherInterface;
use Akeneo\Component\FileStorage\RawFile\RawFileStorerInterface;
use League\Flysystem\MountManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

class FilePublisherSpec extends ObjectBehavior
{
    function let(
        RawFileFetcherInterface $rawFileFetcher,
        RawFileStorerInterface $rawFileStorer,
        MountManager $mountManager
    ) {
        $this->beConstructedWith(
            $rawFileFetcher,
            $rawFileStorer,
            $mountManager
        );
    }

    function it_is_a_publisher()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Publisher\PublisherInterface');
    }

    function it_supports_file(FileInterface $file)
    {
        $this->supports($file)->shouldBe(true);
    }

    function it_publishes_file(FileInterface $file, ProductInterface $product, ProductValueInterface $value)
    {
        $options = ['product' => $product, 'value' => $value];
        $this
            ->publish($file, $options)
            ->shouldReturnAnInstanceOf('Akeneo\Component\FileStorage\Model\FileInterface');
    }
}
