<?php

namespace Specification\Akeneo\Pim\Enrichment\AssetManager\Component\Connector\Writer\File\Csv;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\NumberAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\AssetManager\Domain\Query\Channel\FindActivatedLocalesPerChannelsInterface;
use Akeneo\Pim\Enrichment\AssetManager\Component\Connector\Writer\File\AbstractAssetWriter;
use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\InvalidItemInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Buffer\BufferFactory;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\AbstractFileWriter;
use Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\FileExporterPathGeneratorInterface;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBuffer;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBufferFlusher;
use Akeneo\Tool\Component\Connector\Writer\File\WrittenFileInfo;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use League\Flysystem\FilesystemInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AssetWriterSpec extends ObjectBehavior
{
    private string $directory;

    function let(
        ArrayConverterInterface $arrayConverter,
        BufferFactory $bufferFactory,
        FlatItemBufferFlusher $flusher,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier,
        FindActivatedLocalesPerChannelsInterface $findActivatedLocalesPerChannels,
        FileExporterPathGeneratorInterface $fileExporterPath,
        FlatItemBuffer $flatRowBuffer,
        FileInfoRepositoryInterface $fileInfoRepository,
        FilesystemProvider $filesystemProvider,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        JobExecution $jobExecution,
        ExecutionContext $executionContext,
        MediaFileAttribute $scopableLocalizableAttribute,
        NumberAttribute $scopableAttribute,
        TextAttribute $localizableAttribute,
        MediaLinkAttribute $nonScopableNonLocalizableAttribute
    ) {
        $this->directory = '/tmp/spec/csv_asset_export/';

        $this->beConstructedWith(
            $arrayConverter,
            $bufferFactory,
            $flusher,
            $findAttributesIndexedByIdentifier,
            $findActivatedLocalesPerChannels,
            $fileExporterPath,
            $fileInfoRepository,
            $filesystemProvider
        );

        $executionContext->get(JobInterface::WORKING_DIRECTORY_PARAMETER)->willReturn(
            $this->directory . 'akeneo_batch1234/'
        );
        $jobExecution->getExecutionContext()->willReturn($executionContext);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobParameters->get('asset_family_identifier')->willReturn('packshot');
        $jobParameters->get('filePath')->willReturn($this->directory . 'export_assets.csv');
        $jobParameters->get('delimiter')->willReturn(';');
        $jobParameters->get('enclosure')->willReturn('"');
        $jobParameters->has('linesPerFile')->willReturn(false);
        $jobParameters->get('with_prefix_suffix')->willReturn(false);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $this->setStepExecution($stepExecution);

        $bufferFactory->create()->willReturn($flatRowBuffer);
        $findAttributesIndexedByIdentifier->find(AssetFamilyIdentifier::fromString('packshot'))->willReturn(
            [
                'mainmedia_packshot_123abc' => $scopableLocalizableAttribute,
                'number_packshot_654321' => $scopableAttribute,
                'label_packshot_456321' => $localizableAttribute,
                'youtube_packshot_cdefab' => $nonScopableNonLocalizableAttribute,
            ]
        );
        $this->initialize();
    }

    function it_is_a_file_writer()
    {
        $this->shouldBeAnInstanceOf(AbstractFileWriter::class);
        $this->shouldImplement(ItemWriterInterface::class);
    }

    function it_is_an_asset_file_writer()
    {
        $this->shouldImplement(FlushableInterface::class);
        $this->shouldImplement(ArchivableWriterInterface::class);
        $this->shouldBeAnInstanceOf(AbstractAssetWriter::class);
    }

    function it_writes_items_to_the_file_buffer(
        ArrayConverterInterface $arrayConverter,
        FlatItemBuffer $flatRowBuffer,
        JobParameters $jobParameters
    ) {
        $jobParameters->get('withHeader')->willReturn(true);
        $jobParameters->has('with_media')->willReturn(false);

        $normalizedAssets = [
            [
                'identifier' => 'test_identifier_1',
                'code' => 'asset_code_1',
                'assetFamilyIdentifier' => 'packshot',
                'values' => ['normalized_values_1'],
            ],
            [
                'identifier' => 'test_identifier_2',
                'code' => 'asset_code_2',
                'assetFamilyIdentifier' => 'packshot',
                'values' => ['normalized_values_2'],
            ],
        ];
        $arrayConverter->convert($normalizedAssets[0], ['with_prefix_suffix' => false])->willReturn(
            ['converted_asset_1']
        );
        $arrayConverter->convert($normalizedAssets[1], ['with_prefix_suffix' => false])->willReturn(
            ['converted_asset_2']
        );

        $flatRowBuffer->write([['converted_asset_1'], ['converted_asset_2']], ['withHeader' => true])->shouldBeCalled();

        $this->write($normalizedAssets);
    }

    function it_adds_missing_headers_and_flushes_buffer_to_the_target_file(
        FlatItemBufferFlusher $flusher,
        FlatItemBuffer $flatRowBuffer,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier,
        FindActivatedLocalesPerChannelsInterface $findActivatedLocalesPerChannels,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        AbstractAttribute $scopableLocalizableAttribute,
        AbstractAttribute $scopableAttribute,
        AbstractAttribute $localizableAttribute,
        AbstractAttribute $nonScopableNonLocalizableAttribute
    ) {
        $jobParameters->has('with_media')->willReturn(false);
        $jobParameters->get('withHeader')->willReturn(true);

        $findActivatedLocalesPerChannels->findAll()->willReturn(
            [
                'ecommerce' => ['en_US', 'fr_FR'],
                'mobile' => ['de_DE'],
            ]
        );

        $scopableLocalizableAttribute->getCode()->willReturn(AttributeCode::fromString('scopable_and_localizable'));
        $scopableLocalizableAttribute->hasValuePerChannel()->willReturn(true);
        $scopableLocalizableAttribute->hasValuePerLocale()->willReturn(true);

        $scopableAttribute->getCode()->willReturn(AttributeCode::fromString('scopable'));
        $scopableAttribute->hasValuePerChannel()->willReturn(true);
        $scopableAttribute->hasValuePerLocale()->willReturn(false);

        $localizableAttribute->getCode()->willReturn(AttributeCode::fromString('localizable'));
        $localizableAttribute->hasValuePerChannel()->willReturn(false);
        $localizableAttribute->hasValuePerLocale()->willReturn(true);

        $nonScopableNonLocalizableAttribute->getCode()->willReturn(AttributeCode::fromString('simple'));
        $nonScopableNonLocalizableAttribute->hasValuePerChannel()->willReturn(false);
        $nonScopableNonLocalizableAttribute->hasValuePerLocale()->willReturn(false);

        $findAttributesIndexedByIdentifier->find(AssetFamilyIdentifier::fromString('packshot'))->willReturn(
            [
                $scopableLocalizableAttribute,
                $scopableAttribute,
                $localizableAttribute,
                $nonScopableNonLocalizableAttribute,
            ]
        );

        $flatRowBuffer->addToHeaders(
            [
                'scopable_and_localizable-en_US-ecommerce',
                'scopable_and_localizable-fr_FR-ecommerce',
                'scopable_and_localizable-de_DE-mobile',
                'scopable-ecommerce',
                'scopable-mobile',
                'localizable-en_US',
                'localizable-fr_FR',
                'localizable-de_DE',
                'simple',
            ]
        )->shouldBeCalled();

        $flusher->setStepExecution($stepExecution)->shouldBeCalled();
        $flusher->flush(
            $flatRowBuffer,
            [
                'type' => 'csv',
                'fieldDelimiter' => ';',
                'fieldEnclosure' => '"',
                'shouldAddBOM' => false,
            ],
            $this->directory . 'export_assets.csv',
            -1
        )->shouldBeCalled()->willReturn(
            [
                $this->directory . 'export_assets_1.csv',
                $this->directory . 'export_assets_2.csv',
            ]
        );

        $this->flush();

        $this->getWrittenFiles()->shouldBeLike(
            [
                WrittenFileInfo::fromLocalFile($this->directory . 'export_assets_1.csv', 'export_assets_1.csv'),
                WrittenFileInfo::fromLocalFile($this->directory . 'export_assets_2.csv', 'export_assets_2.csv'),
            ]
        );
    }

    function it_resolves_the_media_file_paths(
        ArrayConverterInterface $arrayConverter,
        FileExporterPathGeneratorInterface $fileExporterPath,
        FlatItemBuffer $flatRowBuffer,
        FileInfoRepositoryInterface $fileInfoRepository,
        FilesystemProvider $filesystemProvider,
        JobParameters $jobParameters,
        MediaFileAttribute $scopableLocalizableAttribute,
        FileInfoInterface $fileInfo,
        FilesystemInterface $assetFilesystem
    ) {
        $jobParameters->has('with_media')->willReturn(true);
        $jobParameters->get('with_media')->willReturn(true);
        $jobParameters->get('withHeader')->willReturn(false);

        $scopableLocalizableAttribute->getCode()->willReturn(AttributeCode::fromString('mainmedia'));

        $assetMediaPath = 'files/asset_code_1/mainmedia/en_US/ecommerce/';
        $normalizedAssets = [
            [
                'identifier' => 'test_identifier_1',
                'code' => 'asset_code_1',
                'assetFamilyIdentifier' => 'packshot',
                'values' => [
                    'mainmedia_packshot_en_US_ecommerce_123abc' => [
                        'attribute' => 'mainmedia_packshot_123abc',
                        'locale' => 'en_US',
                        'channel' => 'ecommerce',
                        'data' => [
                            'filePath' => '1/2/3/jambon987654.jpg',
                        ],
                    ],
                ],
            ],
        ];

        $fileExporterPath->generate(
            [
                'scope' => 'ecommerce',
                'locale' => 'en_US',
            ],
            [
                'identifier' => 'asset_code_1',
                'code' => 'mainmedia',
            ]
        )->willReturn($assetMediaPath);

        $fileInfo->getKey()->willReturn('1/2/3/jambon987654.jpg');
        $fileInfo->getStorage()->willReturn('assetsStorage');
        $fileInfo->getOriginalFilename()->willReturn('jambon.jpg');
        $fileInfoRepository->findOneByIdentifier('1/2/3/jambon987654.jpg')->shouldBeCalled()->willReturn($fileInfo);

        $filesystemProvider->getFilesystem('assetsStorage')->willReturn($assetFilesystem);
        $assetFilesystem->has('1/2/3/jambon987654.jpg')->shouldBeCalled()->willReturn(true);

        $arrayConverter->convert(
            [
                'identifier' => 'test_identifier_1',
                'code' => 'asset_code_1',
                'assetFamilyIdentifier' => 'packshot',
                'values' => [
                    'mainmedia_packshot_en_US_ecommerce_123abc' => [
                        'attribute' => 'mainmedia_packshot_123abc',
                        'locale' => 'en_US',
                        'channel' => 'ecommerce',
                        'data' => [
                            'filePath' => 'files/asset_code_1/mainmedia/en_US/ecommerce/jambon.jpg',
                        ],
                    ],
                ],
            ],
            ['with_prefix_suffix' => false]
        )->shouldBeCalled()->willReturn(['converted_item']);
        $flatRowBuffer->write([['converted_item']], ['withHeader' => false])->shouldBeCalled();

        $this->write($normalizedAssets);
        $this->getWrittenFiles()->shouldBeLike(
            [
                WrittenFileInfo::fromFileStorage(
                    '1/2/3/jambon987654.jpg',
                    'assetsStorage',
                    'files/asset_code_1/mainmedia/en_US/ecommerce/jambon.jpg'
                ),
            ]
        );
    }

    function it_adds_a_warning_if_the_remote_file_does_not_exist(
        ArrayConverterInterface $arrayConverter,
        FileExporterPathGeneratorInterface $fileExporterPath,
        FlatItemBuffer $flatRowBuffer,
        FileInfoRepositoryInterface $fileInfoRepository,
        FilesystemProvider $filesystemProvider,
        JobParameters $jobParameters,
        MediaFileAttribute $scopableLocalizableAttribute,
        FileInfoInterface $fileInfo,
        FilesystemInterface $assetFilesystem,
        StepExecution $stepExecution
    )
    {
        $jobParameters->has('with_media')->willReturn(true);
        $jobParameters->get('with_media')->willReturn(true);
        $jobParameters->get('withHeader')->willReturn(false);

        $scopableLocalizableAttribute->getCode()->willReturn(AttributeCode::fromString('mainmedia'));

        $assetMediaPath = 'files/asset_code_1/mainmedia/en_US/ecommerce/';
        $normalizedAssets = [
            [
                'identifier' => 'test_identifier_1',
                'code' => 'asset_code_1',
                'assetFamilyIdentifier' => 'packshot',
                'values' => [
                    'mainmedia_packshot_en_US_ecommerce_123abc' => [
                        'attribute' => 'mainmedia_packshot_123abc',
                        'locale' => 'en_US',
                        'channel' => 'ecommerce',
                        'data' => [
                            'filePath' => '1/2/3/jambon987654.jpg',
                        ],
                    ],
                ],
            ],
        ];

        $fileExporterPath->generate(
            [
                'scope' => 'ecommerce',
                'locale' => 'en_US',
            ],
            [
                'identifier' => 'asset_code_1',
                'code' => 'mainmedia',
            ]
        )->willReturn($assetMediaPath);

        $fileInfo->getKey()->willReturn('1/2/3/jambon987654.jpg');
        $fileInfo->getStorage()->willReturn('assetsStorage');
        $fileInfo->getOriginalFilename()->willReturn('jambon.jpg');
        $fileInfoRepository->findOneByIdentifier('1/2/3/jambon987654.jpg')->shouldBeCalled()->willReturn($fileInfo);

        $filesystemProvider->getFilesystem('assetsStorage')->willReturn($assetFilesystem);
        $assetFilesystem->has('1/2/3/jambon987654.jpg')->shouldBeCalled()->willReturn(false);

        $stepExecution->addWarning(
            'The media has not been found or is not currently available',
            [],
            Argument::that(
                function ($invalidItem): bool {
                    return $invalidItem instanceof InvalidItemInterface &&
                        $invalidItem->getInvalidData() === [
                            'from' => '1/2/3/jambon987654.jpg',
                            'to' => [
                                'filePath' => 'files/asset_code_1/mainmedia/en_US/ecommerce',
                                'filename' => 'jambon.jpg',
                            ],
                            'storage' => 'assetsStorage'
                        ];
                }
            )
        )->shouldBeCalled();

        $arrayConverter->convert(
            [
                'identifier' => 'test_identifier_1',
                'code' => 'asset_code_1',
                'assetFamilyIdentifier' => 'packshot',
                'values' => [
                    'mainmedia_packshot_en_US_ecommerce_123abc' => [
                        'attribute' => 'mainmedia_packshot_123abc',
                        'locale' => 'en_US',
                        'channel' => 'ecommerce',
                        'data' => [
                            'filePath' => '1/2/3/jambon987654.jpg',
                        ],
                    ],
                ],
            ],
            ['with_prefix_suffix' => false]
        )->shouldBeCalled()->willReturn(['converted_item']);
        $flatRowBuffer->write([['converted_item']], ['withHeader' => false])->shouldBeCalled();

        $this->write($normalizedAssets);
        $this->getWrittenFiles()->shouldReturn([]);
    }
}
