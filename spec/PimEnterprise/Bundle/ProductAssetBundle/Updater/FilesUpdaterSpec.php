<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\Updater;

use Akeneo\Component\FileStorage\Model\FileInterface;
use Akeneo\Component\FileStorage\RawFile\RawFileStorerInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FilesUpdaterSpec extends ObjectBehavior
{
    function let(
        EventDispatcherInterface $eventDispatcher,
        RawFileStorerInterface $rawFileStorer
    ) {
        $this->beConstructedWith($eventDispatcher, $rawFileStorer);
    }

    function it_can_be_initialized()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\ProductAssetBundle\Updater\FilesUpdater');
    }

    function it_can_update_asset_files()
    {
        $this->shouldImplement('PimEnterprise\Bundle\ProductAssetBundle\Updater\FilesUpdaterInterface');
    }

    function it_can_delete_reference_file(ReferenceInterface $reference, FileInterface $file)
    {
        $reference->getFile()->willReturn($file);
        $reference->setFile(null)->shouldBeCalled();

        $this->deleteReferenceFile($reference);
    }

    function it_can_reset_variation_file(VariationInterface $variation, ReferenceInterface $reference, FileInterface $file)
    {
        $variation->getReference()->willReturn($reference);
        $reference->getFile()->willReturn($file);

        $variation->setFile(null)->shouldBeCalled();
        $variation->setLocked(false)->shouldBeCalled();
        $variation->setSourceFile($file)->shouldBeCalled();

        $this->resetVariationFile($variation);
    }

    function it_can_delete_variation_file(VariationInterface $variation)
    {
        $variation->setFile(null)->shouldBeCalled();
        $variation->setLocked(true)->shouldBeCalled();
        $variation->setSourceFile(null)->shouldBeCalled();

        $this->deleteVariationFile($variation);
    }
}
