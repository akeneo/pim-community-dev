<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Enrichment\AssetManager\Component\Processor;

use Akeneo\AssetManager\Infrastructure\Filesystem\Storage;
use Akeneo\Tool\Component\Connector\Writer\File\MediaExporterPathGenerator;
use Akeneo\Tool\Component\FileStorage\File\FileFetcherInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\FilesystemInterface;
use PhpSpec\ObjectBehavior;

class BulkAssetMediaFetcherSpec extends ObjectBehavior
{
    function let(FileFetcherInterface $mediaFetcher, FilesystemProvider $filesystemProvider)
    {
        $this->beConstructedWith($mediaFetcher, $filesystemProvider, new MediaExporterPathGenerator());
    }

    function it_fetches_asset_main_media_for_non_localizable_scopable_product_value(
        FileFetcherInterface $mediaFetcher,
        FilesystemProvider $filesystemProvider,
        FilesystemInterface $filesystem
    ) {
        $productValue = ['scope' => null, 'locale' => null];
        $assetMediaValues = $this->givenSomeAssetMainMedia();

        $filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS)->willReturn($filesystem);
        $mediaFetcher->fetch($filesystem, 'filePath1', [
            'filePath' => '/tmp/files/product1/asset_attr/',
            'filename' => 'file_01.jpg',
        ])->shouldBeCalled();
        $mediaFetcher->fetch($filesystem, 'filePath2', [
            'filePath' => '/tmp/files/product1/asset_attr/',
            'filename' => 'file_02.jpg',
        ])->shouldBeCalled();

        $this->fetchAllForAssetRawValuesAndReturnPaths(
            $productValue,
            $assetMediaValues,
            '/tmp/',
            'product1',
            'asset_attr'
        )->shouldReturn([
            'files/product1/asset_attr/file_01.jpg',
            'files/product1/asset_attr/file_02.jpg',
            'a link',
        ]);
    }

    function it_fetches_asset_main_media_for_localizable_product_value(
        FileFetcherInterface $mediaFetcher,
        FilesystemProvider $filesystemProvider,
        FilesystemInterface $filesystem
    ) {
        $productValue = ['scope' => null, 'locale' => 'en_US'];
        $assetMediaValues = $this->givenSomeAssetMainMedia();

        $filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS)->willReturn($filesystem);
        $mediaFetcher->fetch($filesystem, 'filePath1', [
            'filePath' => '/tmp/files/product1/asset_attr/en_US/',
            'filename' => 'file_01.jpg',
        ])->shouldBeCalled();
        $mediaFetcher->fetch($filesystem, 'filePath2', [
            'filePath' => '/tmp/files/product1/asset_attr/en_US/',
            'filename' => 'file_02.jpg',
        ])->shouldBeCalled();

        $this->fetchAllForAssetRawValuesAndReturnPaths(
            $productValue,
            $assetMediaValues,
            '/tmp/',
            'product1',
            'asset_attr'
        )->shouldReturn([
            'files/product1/asset_attr/en_US/file_01.jpg',
            'files/product1/asset_attr/en_US/file_02.jpg',
            'a link',
        ]);
    }

    function it_fetches_asset_main_media_for_scopable_product_value(
        FileFetcherInterface $mediaFetcher,
        FilesystemProvider $filesystemProvider,
        FilesystemInterface $filesystem
    ) {
        $productValue = ['scope' => 'mobile', 'locale' => null];
        $assetMediaValues = $this->givenSomeAssetMainMedia();

        $filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS)->willReturn($filesystem);
        $mediaFetcher->fetch($filesystem, 'filePath1', [
            'filePath' => '/tmp/files/product1/asset_attr/mobile/',
            'filename' => 'file_01.jpg',
        ])->shouldBeCalled();
        $mediaFetcher->fetch($filesystem, 'filePath2', [
            'filePath' => '/tmp/files/product1/asset_attr/mobile/',
            'filename' => 'file_02.jpg',
        ])->shouldBeCalled();

        $this->fetchAllForAssetRawValuesAndReturnPaths(
            $productValue,
            $assetMediaValues,
            '/tmp/',
            'product1',
            'asset_attr'
        )->shouldReturn([
            'files/product1/asset_attr/mobile/file_01.jpg',
            'files/product1/asset_attr/mobile/file_02.jpg',
            'a link',
        ]);
    }

    function it_fetches_asset_main_media_for_localizable_and_scopable_product_value(
        FileFetcherInterface $mediaFetcher,
        FilesystemProvider $filesystemProvider,
        FilesystemInterface $filesystem
    ) {
        $productValue = ['scope' => 'mobile', 'locale' => 'en_US'];
        $assetMediaValues = $this->givenSomeAssetMainMedia();

        $filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS)->willReturn($filesystem);
        $mediaFetcher->fetch($filesystem, 'filePath1', [
            'filePath' => '/tmp/files/product1/asset_attr/en_US/mobile/',
            'filename' => 'file_01.jpg',
        ])->shouldBeCalled();
        $mediaFetcher->fetch($filesystem, 'filePath2', [
            'filePath' => '/tmp/files/product1/asset_attr/en_US/mobile/',
            'filename' => 'file_02.jpg',
        ])->shouldBeCalled();

        $this->fetchAllForAssetRawValuesAndReturnPaths(
            $productValue,
            $assetMediaValues,
            '/tmp/',
            'product1',
            'asset_attr'
        )->shouldReturn([
            'files/product1/asset_attr/en_US/mobile/file_01.jpg',
            'files/product1/asset_attr/en_US/mobile/file_02.jpg',
            'a link',
        ]);
    }

    private function givenSomeAssetMainMedia(): array
    {
        return [
            [
                'locale' => 'en_US',
                'channel' => null,
                'data' => ['filePath' => 'filePath1', 'originalFilename' => 'file_01.jpg'],
            ],
            [
                'locale' => null,
                'channel' => 'mobile',
                'data' => ['filePath' => 'filePath2', 'originalFilename' => 'file_02.jpg'],
            ],
            [
                'locale' => null,
                'channel' => 'mobile',
                'data' => 'a link',
            ],
        ];
    }
}
