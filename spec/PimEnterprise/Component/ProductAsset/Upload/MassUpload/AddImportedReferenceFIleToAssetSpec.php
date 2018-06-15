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

namespace spec\PimEnterprise\Component\ProductAsset\Upload\MassUpload;

use Akeneo\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Factory\AssetFactory;
use PimEnterprise\Component\ProductAsset\FileStorage;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use PimEnterprise\Component\ProductAsset\Updater\FilesUpdaterInterface;
use PimEnterprise\Component\ProductAsset\Upload\Exception\UploadException;
use PimEnterprise\Component\ProductAsset\Upload\ParsedFilenameInterface;
use PimEnterprise\Component\ProductAsset\Upload\MassUpload\AddImportedReferenceFIleToAsset;
use PimEnterprise\Component\ProductAsset\Upload\UploadCheckerInterface;
use Prophecy\Argument;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class AddImportedReferenceFIleToAssetSpec extends ObjectBehavior
{
    function let(
        UploadCheckerInterface $uploadChecker,
        AssetFactory $assetFactory,
        IdentifiableObjectRepositoryInterface $assetRepository,
        FilesUpdaterInterface $filesUpdater,
        FileStorerInterface $fileStorer,
        LocaleRepositoryInterface $localeRepository
    ) {
        $this->beConstructedWith(
            $uploadChecker,
            $assetFactory,
            $assetRepository,
            $filesUpdater,
            $fileStorer,
            $localeRepository
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AddImportedReferenceFIleToAsset::class);
    }

    function it_adds_an_imported_file_to_an_asset(
        $uploadChecker,
        $assetFactory,
        $assetRepository,
        $filesUpdater,
        $fileStorer,
        $localeRepository,
        ParsedFilenameInterface $parsedFilename,
        AssetInterface $asset,
        FileInfoInterface $storedFile,
        ReferenceInterface $assetReference
    ) {
        $file = new \SplFileInfo('file_name.jpg');

        $uploadChecker->getParsedFilename('file_name.jpg')->willReturn($parsedFilename);
        $uploadChecker->validateFilenameFormat($parsedFilename)->shouldBeCalled();
        $parsedFilename->getAssetCode()->willReturn('file_name');
        $parsedFilename->getLocaleCode()->willReturn(null);
        $localeRepository->findOneBy(Argument::any())->shouldNotBeCalled();

        $assetRepository->findOneByIdentifier('file_name')->willReturn($asset);
        $assetFactory->create()->shouldNotBeCalled();
        $assetFactory->createReferences(Argument::cetera())->shouldNotBeCalled();

        $fileStorer->store($file, FileStorage::ASSET_STORAGE_ALIAS, true)->willReturn($storedFile);

        $asset->getReference(null)->willReturn($assetReference);
        $assetReference->setFileInfo($storedFile)->shouldBeCalled();
        $filesUpdater->resetAllVariationsFiles($assetReference, true)->shouldBeCalled();
        $filesUpdater->updateAssetFiles($asset)->shouldBeCalled();

        $this->addFile($file)->shouldReturn($asset);
    }

    function it_adds_an_imported_file_to_a_new_asset(
        $uploadChecker,
        $assetFactory,
        $assetRepository,
        $filesUpdater,
        $fileStorer,
        $localeRepository,
        ParsedFilenameInterface $parsedFilename,
        AssetInterface $asset,
        FileInfoInterface $storedFile,
        ReferenceInterface $assetReference
    ) {
        $file = new \SplFileInfo('file_name.jpg');

        $uploadChecker->getParsedFilename('file_name.jpg')->willReturn($parsedFilename);
        $uploadChecker->validateFilenameFormat($parsedFilename)->shouldBeCalled();
        $parsedFilename->getAssetCode()->willReturn('file_name');
        $parsedFilename->getLocaleCode()->willReturn(null);
        $localeRepository->findOneBy(Argument::any())->shouldNotBeCalled();

        $assetRepository->findOneByIdentifier('file_name')->willReturn(null);
        $assetFactory->create()->willReturn($asset);
        $asset->setCode('file_name')->shouldBeCalled();
        $assetFactory->createReferences($asset, false)->shouldBeCalled();

        $fileStorer->store($file, FileStorage::ASSET_STORAGE_ALIAS, true)->willReturn($storedFile);

        $asset->getReference(null)->willReturn($assetReference);
        $assetReference->setFileInfo($storedFile)->shouldBeCalled();
        $filesUpdater->resetAllVariationsFiles($assetReference, true)->shouldBeCalled();
        $filesUpdater->updateAssetFiles($asset)->shouldBeCalled();

        $this->addFile($file)->shouldReturn($asset);
    }

    function it_adds_an_imported_file_to_a_new_localized_asset(
        $uploadChecker,
        $assetFactory,
        $assetRepository,
        $filesUpdater,
        $fileStorer,
        $localeRepository,
        ParsedFilenameInterface $parsedFilename,
        AssetInterface $asset,
        FileInfoInterface $storedFile,
        ReferenceInterface $assetReference,
        LocaleInterface $locale
    ) {
        $file = new \SplFileInfo('file_name-en_US.jpg');

        $uploadChecker->getParsedFilename('file_name-en_US.jpg')->willReturn($parsedFilename);
        $uploadChecker->validateFilenameFormat($parsedFilename)->shouldBeCalled();
        $parsedFilename->getAssetCode()->willReturn('file_name');
        $parsedFilename->getLocaleCode()->willReturn('en_US');
        $localeRepository->findOneBy(['code' => 'en_US'])->willReturn($locale);

        $assetRepository->findOneByIdentifier('file_name')->willReturn(null);
        $assetFactory->create()->willReturn($asset);
        $asset->setCode('file_name')->shouldBeCalled();
        $assetFactory->createReferences($asset, true)->shouldBeCalled();

        $fileStorer->store($file, FileStorage::ASSET_STORAGE_ALIAS, true)->willReturn($storedFile);

        $asset->getReference($locale)->willReturn($assetReference);
        $assetReference->setFileInfo($storedFile)->shouldBeCalled();
        $filesUpdater->resetAllVariationsFiles($assetReference, true)->shouldBeCalled();
        $filesUpdater->updateAssetFiles($asset)->shouldBeCalled();

        $this->addFile($file)->shouldReturn($asset);
    }

    function it_does_not_add_an_imported_file_to_an_asset_without_reference(
        $uploadChecker,
        $assetFactory,
        $assetRepository,
        $filesUpdater,
        $fileStorer,
        $localeRepository,
        ParsedFilenameInterface $parsedFilename,
        AssetInterface $asset,
        FileInfoInterface $storedFile
    ) {
        $file = new \SplFileInfo('file_name.jpg');

        $uploadChecker->getParsedFilename('file_name.jpg')->willReturn($parsedFilename);
        $uploadChecker->validateFilenameFormat($parsedFilename)->shouldBeCalled();
        $parsedFilename->getAssetCode()->willReturn('file_name');
        $parsedFilename->getLocaleCode()->willReturn(null);
        $localeRepository->findOneBy(Argument::any())->shouldNotBeCalled();

        $assetRepository->findOneByIdentifier('file_name')->willReturn($asset);
        $assetFactory->create()->shouldNotBeCalled();
        $assetFactory->createReferences(Argument::cetera())->shouldNotBeCalled();

        $fileStorer->store($file, FileStorage::ASSET_STORAGE_ALIAS, true)->willReturn($storedFile);

        $asset->getReference(null)->willReturn(null);
        $filesUpdater->resetAllVariationsFiles(Argument::cetera())->shouldNotBeCalled();
        $filesUpdater->updateAssetFiles($asset)->shouldBeCalled();

        $this->addFile($file)->shouldReturn($asset);
    }

    function it_throws_an_exception_if_parsed_file_name_is_not_valid(
        $uploadChecker,
        ParsedFilenameInterface $parsedFilename
    ) {
        $file = new \SplFileInfo('file_name.jpg');

        $uploadChecker->getParsedFilename('file_name.jpg')->willReturn($parsedFilename);
        $uploadChecker->validateFilenameFormat($parsedFilename)->willThrow(UploadException::class);

        $this->shouldThrow(UploadException::class)->during('addFile', [$file]);
    }
}
