<?php

namespace Specification\Akeneo\Asset\Component\Updater;

use Akeneo\Asset\Component\Updater\FilesUpdater;
use Akeneo\Asset\Component\Updater\FilesUpdaterInterface;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Asset\Component\Model\ReferenceInterface;
use Akeneo\Asset\Component\Model\VariationInterface;

class FilesUpdaterSpec extends ObjectBehavior
{
    function let(
        FileStorerInterface $fileStorer
    ) {
        $this->beConstructedWith($fileStorer);
    }

    function it_can_be_initialized()
    {
        $this->shouldHaveType(FilesUpdater::class);
    }

    function it_can_update_asset_files()
    {
        $this->shouldImplement(FilesUpdaterInterface::class);
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

        $this->deleteVariationFile($variation);
    }
}
