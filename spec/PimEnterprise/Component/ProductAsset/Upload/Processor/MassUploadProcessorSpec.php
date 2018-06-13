<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\PimEnterprise\Component\ProductAsset\Upload\Processor;

use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\ProductAssetBundle\Event\AssetEvent;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\ProcessedItem;
use PimEnterprise\Component\ProductAsset\ProcessedItemList;
use PimEnterprise\Component\ProductAsset\Upload\ImporterInterface;
use PimEnterprise\Component\ProductAsset\Upload\Processor\AddImportedReferenceFIleToAsset;
use PimEnterprise\Component\ProductAsset\Upload\Processor\MassUploadProcessor;
use PimEnterprise\Component\ProductAsset\Upload\Processor\MassUploadProcessorInterface;
use PimEnterprise\Component\ProductAsset\Upload\UploadContext;
use PimEnterprise\Component\ProductAsset\Upload\UploadMessages;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class MassUploadProcessorSpec extends ObjectBehavior
{
    function let(
        ImporterInterface $importer,
        AddImportedReferenceFIleToAsset $addImportedReferenceFIleToAsset,
        SaverInterface $assetSaver,
        EventDispatcherInterface $eventDispatcher,
        TranslatorInterface $translator,
        ObjectDetacherInterface $objectDetacher
    ) {
        $this->beConstructedWith(
            $importer,
            $addImportedReferenceFIleToAsset,
            $assetSaver,
            $eventDispatcher,
            $translator,
            $objectDetacher
        );
    }

    function it_it_a_mass_upload_processor()
    {
        $this->shouldHaveType(MassUploadProcessor::class);
        $this->shouldImplement(MassUploadProcessorInterface::class);
    }

    function it_mass_upload_asset_file(
        $importer,
        $addImportedReferenceFIleToAsset,
        $assetSaver,
        $eventDispatcher,
        $objectDetacher,
        \SplFileInfo $importedFile,
        AssetInterface $asset
    ) {
        $uploadContext = new UploadContext('/tmp/pim/file_storage', 'username');

        $importer->getImportedFiles($uploadContext)->willReturn([$importedFile]);
        $addImportedReferenceFIleToAsset->addFile($importedFile)->willReturn($asset); // TODO variant exception
        $asset->getId()->willReturn(42); // TODO variant null
        $assetSaver->save($asset)->shouldBeCalled();

        $eventItems = new ProcessedItemList();
        $eventItems->addItem($importedFile, ProcessedItem::STATE_SUCCESS, ''); // TODO variants errors
        $event = new AssetEvent($asset);
        $event->setProcessedList($eventItems);
        $eventDispatcher->dispatch(AssetEvent::POST_UPLOAD_FILES, $event)->willReturn($event);

        $objectDetacher->detach($asset);

        $this->process($uploadContext)->beAnInstanceOf(ProcessedItemList::class);
    }
}
