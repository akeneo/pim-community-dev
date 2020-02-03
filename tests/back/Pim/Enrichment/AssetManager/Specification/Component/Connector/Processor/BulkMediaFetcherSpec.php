<?php

namespace Specification\Akeneo\Pim\Enrichment\AssetManager\Component\Connector\Processor;

use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\EmptyData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Filesystem\Storage;
use Akeneo\Pim\Enrichment\AssetManager\Component\Connector\Processor\BulkMediaFetcher;
use Akeneo\Tool\Component\Connector\Writer\File\FileExporterPathGeneratorInterface;
use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Tool\Component\FileStorage\File\FileFetcherInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\FilesystemInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class BulkMediaFetcherSpec extends ObjectBehavior
{
    function let(
        FileFetcherInterface $mediaFetcher,
        FileExporterPathGeneratorInterface $fileExporterPath,
        FilesystemProvider $filesystemProvider,
        AttributeRepositoryInterface $attributeRepository,
        FilesystemInterface $filesystem
    ) {
        $filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS)->willReturn($filesystem);

        $this->beConstructedWith($mediaFetcher, $fileExporterPath, $filesystemProvider, $attributeRepository);
    }

    function it_is_a_bulk_media_fetcher()
    {
        $this->shouldHaveType(BulkMediaFetcher::class);
    }

    function it_only_fetches_media_from_media_file_values(FileFetcherInterface $mediaFetcher)
    {
        $values = ValueCollection::fromValues(
            [
                Value::create(
                    AttributeIdentifier::fromString('attr'),
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    EmptyData::create()
                )
            ]
        );
        $mediaFetcher->fetch(Argument::cetera())->shouldNotBeCalled();

        $this->fetchAll($values, '/tmp/akeneo_batch1234', 'asset_1');
    }

    function it_fetches_media_files_into_a_specific_directory(
        FileFetcherInterface $mediaFetcher,
        FileExporterPathGeneratorInterface $fileExporterPath,
        AttributeRepositoryInterface $attributeRepository,
        FilesystemInterface $filesystem,
        MediaFileAttribute $image
    ) {
        $attributeIdentifier = AttributeIdentifier::fromString('image_packshot_1234');
        $image->getCode()->willReturn(AttributeCode::fromString('image'));
        $attributeRepository->getByIdentifier($attributeIdentifier)->willReturn($image);

        $values = ValueCollection::fromValues(
            [
                Value::create(
                    $attributeIdentifier,
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    FileData::createFromNormalize([
                        'filePath' => '1/2/3/jambonabcdef.jpg',
                        'originalFilename' => 'jambon.jpg',
                        'size' => 4096,
                        'mimeType' => 'image/jpg',
                        'extension' => 'jpg',
                        'updatedAt' => '2020-01-01T00:00:00+00:00',
                    ])
                ),

            ]
        );

        $fileExporterPath->generate(
            [
                'locale' => 'en_US',
                'scope' => 'ecommerce',
            ],
            [
                'identifier' => 'asset_code',
                'code' => 'image',
            ]
        )->shouldBeCalled()->willReturn('files/asset_code/image/en_US/ecommerce/');
        $mediaFetcher->fetch(
            $filesystem->getWrappedObject(),
            '1/2/3/jambonabcdef.jpg',
            ['filePath' => '/tmp/akeneo_batch1234/files/asset_code/image/en_US/ecommerce/', 'filename' => 'jambon.jpg']
        )->shouldBeCalled();

        $this->fetchAll($values, '/tmp/akeneo_batch1234', 'asset_code');
    }

    function it_adds_errors_if_fetch_fails(
        FileFetcherInterface $mediaFetcher,
        FileExporterPathGeneratorInterface $fileExporterPath,
        AttributeRepositoryInterface $attributeRepository,
        FilesystemInterface $filesystem,
        MediaFileAttribute $image
    ) {
        $attributeIdentifier = AttributeIdentifier::fromString('image_packshot_1234');
        $image->getCode()->willReturn(AttributeCode::fromString('image'));
        $attributeRepository->getByIdentifier($attributeIdentifier)->shouldBeCalledOnce()->willReturn($image);

        $values = ValueCollection::fromValues(
            [
                Value::create(
                    $attributeIdentifier,
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    FileData::createFromNormalize(
                        [
                            'filePath' => '1/2/3/jambonabcdef.jpg',
                            'originalFilename' => 'jambon.jpg',
                            'size' => 4096,
                            'mimeType' => 'image/jpg',
                            'extension' => 'jpg',
                            'updatedAt' => '2020-01-01T00:00:00+00:00',
                        ]
                    )
                ),
                Value::create(
                    $attributeIdentifier,
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('mobile')),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    FileData::createFromNormalize(
                        [
                            'filePath' => 'a/b/c/tartiflette654321.jpg',
                            'originalFilename' => 'tartiflette.jpg',
                            'size' => 8000,
                            'mimeType' => 'image/jpg',
                            'extension' => 'jpg',
                            'updatedAt' => '2020-01-10T00:00:00+00:00',
                        ]
                    )
                ),
            ]
        );

        $fileExporterPath->generate(
            [
                'locale' => 'en_US',
                'scope' => 'ecommerce',
            ],
            [
                'identifier' => 'asset_code',
                'code' => 'image',
            ]
        )->shouldBeCalled()->willReturn('files/asset_code/image/en_US/ecommerce/');
        $fileExporterPath->generate(
            [
                'locale' => 'en_US',
                'scope' => 'mobile',
            ],
            [
                'identifier' => 'asset_code',
                'code' => 'image',
            ]
        )->shouldBeCalled()->willReturn('files/asset_code/image/en_US/mobile/');

        $mediaFetcher->fetch(
            $filesystem->getWrappedObject(),
            '1/2/3/jambonabcdef.jpg',
            ['filePath' => '/tmp/akeneo_batch1234/files/asset_code/image/en_US/ecommerce/', 'filename' => 'jambon.jpg']
        )->willThrow(new FileTransferException());

        $mediaFetcher->fetch(
            $filesystem->getWrappedObject(),
            'a/b/c/tartiflette654321.jpg',
            ['filePath' => '/tmp/akeneo_batch1234/files/asset_code/image/en_US/mobile/', 'filename' => 'tartiflette.jpg']
        )->willThrow(new \LogicException('Target directory is not writable'));

        $this->fetchAll($values, '/tmp/akeneo_batch1234', 'asset_code');


        $this->getErrors()->shouldReturn([
            [
                'message' => 'The media has not been found or is not currently available',
                'media' => [
                    'from' => '1/2/3/jambonabcdef.jpg',
                    'to' => [
                        'filePath' => '/tmp/akeneo_batch1234/files/asset_code/image/en_US/ecommerce/',
                        'filename' => 'jambon.jpg',
                    ],
                    'storage' => Storage::FILE_STORAGE_ALIAS,
                ]
            ],
            [
                'message' => 'The media has not been copied. Target directory is not writable',
                'media' => [
                    'from' => 'a/b/c/tartiflette654321.jpg',
                    'to' => [
                        'filePath' => '/tmp/akeneo_batch1234/files/asset_code/image/en_US/mobile/',
                        'filename' => 'tartiflette.jpg',
                    ],
                    'storage' => Storage::FILE_STORAGE_ALIAS,
                ]
            ]
        ]);
    }
}
