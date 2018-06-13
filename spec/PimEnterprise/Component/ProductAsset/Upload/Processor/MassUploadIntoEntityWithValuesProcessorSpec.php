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
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use PimEnterprise\Bundle\ProductAssetBundle\Event\AssetEvent;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\ProcessedItem;
use PimEnterprise\Component\ProductAsset\ProcessedItemList;
use PimEnterprise\Component\ProductAsset\Upload\ImporterInterface;
use PimEnterprise\Component\ProductAsset\Upload\Processor\AddAssetsTo;
use PimEnterprise\Component\ProductAsset\Upload\Processor\AddImportedReferenceFIleToAsset;
use PimEnterprise\Component\ProductAsset\Upload\Processor\MassUploadIntoEntityWithValuesProcessor;
use PimEnterprise\Component\ProductAsset\Upload\UploadContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MassUploadIntoEntityWithValuesProcessorSpec extends ObjectBehavior
{
    function let(
        ImporterInterface $importer,
        AddImportedReferenceFIleToAsset $addImportedReferenceFIleToAsset,
        SaverInterface $assetSaver,
        EventDispatcherInterface $eventDispatcher,
        TranslatorInterface $translator,
        ObjectDetacherInterface $objectDetacher,
        ObjectRepository $entityWithValueRepository,
        ObjectUpdaterInterface $entityWithValueUpdater,
        ValidatorInterface $validator,
        SaverInterface $entityWithValueSaver
    ) {
        $this->beConstructedWith(
            $importer,
            $addImportedReferenceFIleToAsset,
            $assetSaver,
            $eventDispatcher,
            $translator,
            $objectDetacher,
            $entityWithValueRepository,
            $entityWithValueUpdater,
            $validator,
            $entityWithValueSaver
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MassUploadIntoEntityWithValuesProcessor::class);
    }

    function it_mass_upload_an_asset_and_add_it_to_an_entity_with_value(
        $importer,
        $addImportedReferenceFIleToAsset,
        $assetSaver,
        $eventDispatcher,
        $objectDetacher,
        $entityWithValueRepository,
        $entityWithValueUpdater,
        $validator,
        $entityWithValueSaver,
        \SplFileInfo $importedFile,
        AssetInterface $asset,
        EntityWithValuesInterface $entityWithValues,
        ConstraintViolationListInterface $errors
    ) {
        $uploadContext = new UploadContext('/tmp/pim/file_storage', 'username');
        $addAssetTo = new AddAssetsTo(666, 'attribute_code');

        $importer->getImportedFiles($uploadContext)->willReturn([$importedFile]);
        $addImportedReferenceFIleToAsset->addFile($importedFile)->willReturn($asset);
        $asset->getId()->willReturn(42);
        $asset->getCode()->willReturn('asset_code');
        $assetSaver->save($asset)->shouldBeCalled();

        $eventItems = new ProcessedItemList();
        $eventItems->addItem($importedFile, ProcessedItem::STATE_SUCCESS, '');
        $event = new AssetEvent($asset);
        $event->setProcessedList($eventItems);
        $eventDispatcher->dispatch(AssetEvent::POST_UPLOAD_FILES, $event)->willReturn($event);

        $objectDetacher->detach($asset);

        $entityWithValueRepository->find(666)->willReturn($entityWithValues);
        $entityWithValues->getValue('attribute_code')->willReturn(null);
        $entityWithValueUpdater->update($entityWithValues, [
            'values' => [
                'attribute_code' => [[
                    'locale' => null,
                    'scope' => null,
                    'data' => ['asset_code'],
                ]],
            ],
        ])->shouldBeCalled();

        $validator->validate($entityWithValues)->willReturn($errors);
        $errors->count()->willReturn(0);

        $entityWithValueSaver->save($entityWithValues)->shouldBeCalled();

        $this->process($uploadContext, $addAssetTo)->beAnInstanceOf(ProcessedItemList::class);
    }
}
