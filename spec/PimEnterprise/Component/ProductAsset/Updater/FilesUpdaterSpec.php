<?php

namespace spec\PimEnterprise\Component\ProductAsset\Updater;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Component\FileStorage\RawFile\RawFileStorerInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;

class FilesUpdaterSpec extends ObjectBehavior
{
    function let(
        RawFileStorerInterface $rawFileStorer
    ) {
        $this->beConstructedWith($rawFileStorer);
    }

    function it_can_be_initialized()
    {
        $this->shouldHaveType('PimEnterprise\Component\ProductAsset\Updater\FilesUpdater');
    }

    function it_can_update_asset_files()
    {
        $this->shouldImplement('PimEnterprise\Component\ProductAsset\Updater\FilesUpdaterInterface');
    }

    function it_can_delete_reference_file(ReferenceInterface $reference, FileInfoInterface $fileInfo)
    {
        $reference->getFileInfo()->willReturn($fileInfo);
        $reference->setFileInfo(null)->shouldBeCalled();

        $this->deleteReferenceFile($reference);
    }

    function it_can_reset_variation_file(
        VariationInterface $variation,
        ReferenceInterface $reference,
        FileInfoInterface $fileInfo
    ) {
        $variation->getReference()->willReturn($reference);
        $reference->getFileInfo()->willReturn($fileInfo);

        $variation->setFileInfo(null)->shouldBeCalled();
        $variation->setLocked(false)->shouldBeCalled();
        $variation->setSourceFileInfo($fileInfo)->shouldBeCalled();

        $this->resetVariationFile($variation);
    }

    function it_can_delete_variation_file(VariationInterface $variation)
    {
        $variation->setFileInfo(null)->shouldBeCalled();
        $variation->setLocked(true)->shouldBeCalled();
        $variation->setSourceFileInfo(null)->shouldBeCalled();

        $this->deleteVariationFile($variation);
    }
}
