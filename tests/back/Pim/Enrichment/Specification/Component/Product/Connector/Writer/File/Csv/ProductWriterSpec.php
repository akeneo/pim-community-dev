<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\Csv;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatTranslatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\Csv\ProductWriter;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\FlatFileHeader;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\GenerateFlatHeadersFromAttributeCodesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\GenerateFlatHeadersFromFamilyCodesInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\InvalidItemInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Buffer\BufferFactory;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\FileExporterPathGeneratorInterface;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBuffer;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBufferFlusher;
use Akeneo\Tool\Component\Connector\Writer\File\WrittenFileInfo;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use League\Flysystem\FilesystemOperator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;

class ProductWriterSpec extends ObjectBehavior
{
    private string $directory;
    private Filesystem $filesystem;

    function let(
        ArrayConverterInterface $arrayConverter,
        BufferFactory $bufferFactory,
        FlatItemBufferFlusher $flusher,
        AttributeRepositoryInterface $attributeRepository,
        FileExporterPathGeneratorInterface $fileExporterPath,
        GenerateFlatHeadersFromFamilyCodesInterface $generateHeadersFromFamilyCodes,
        GenerateFlatHeadersFromAttributeCodesInterface $generateHeadersFromAttributeCodes,
        FlatTranslatorInterface $flatTranslator,
        FileInfoRepositoryInterface $fileInfoRepository,
        FilesystemProvider $filesystemProvider,
        FlatItemBuffer $flatRowBuffer,
        StepExecution $stepExecution
    ) {
        $this->directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'spec' . DIRECTORY_SEPARATOR;
        $this->filesystem = new Filesystem();
        $this->filesystem->mkdir($this->directory);

        $bufferFactory->create()->willReturn($flatRowBuffer);

        $this->beConstructedWith(
            $arrayConverter,
            $bufferFactory,
            $flusher,
            $attributeRepository,
            $fileExporterPath,
            $generateHeadersFromFamilyCodes,
            $generateHeadersFromAttributeCodes,
            $flatTranslator,
            $fileInfoRepository,
            $filesystemProvider,
            ['pim_catalog_file', 'pim_catalog_image']
        );

        $stepExecution->getStartTime()->willReturn(\DateTime::createFromFormat('Y-m-d H:i:s', '2021-03-24 16:00:00'));
        $this->setStepExecution($stepExecution);
    }

    function letGo()
    {
        $this->filesystem->remove($this->directory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductWriter::class);
        $this->shouldImplement(ItemWriterInterface::class);
        $this->shouldImplement(InitializableInterface::class);
        $this->shouldImplement(FlushableInterface::class);
        $this->shouldImplement(StepExecutionAwareInterface::class);
        $this->shouldImplement(ArchivableWriterInterface::class);
    }

    function it_prepares_the_export(
        ArrayConverterInterface $arrayConverter,
        AttributeRepositoryInterface $attributeRepository,
        FileExporterPathGeneratorInterface $fileExporterPath,
        GenerateFlatHeadersFromFamilyCodesInterface $generateHeadersFromFamilyCodes,
        FlatItemBuffer $flatRowBuffer,
        FileInfoRepositoryInterface $fileInfoRepository,
        FilesystemProvider $filesystemProvider,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        FileInfoInterface $fileInfo,
        FilesystemOperator $catalogFilesystem
    ) {
        $jobParameters = new JobParameters(
            [
                'withHeader' => true,
                'storage' => ['type' => 'local', 'file_path' => $this->directory . '%job_label%_product.csv'],
                'with_media' => true,
                'filters' => ['structure' => ['locales' => ['fr_FR', 'en_US'], 'scope' => 'ecommerce']],
            ]
        );
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $productStandard = [
            'uuid' => '47617b09-f227-4ae5-b55d-287cd237cfe6',
            'identifier' => 'jacket',
            'enabled' => true,
            'categories' => ['2015_clothes', '2016_clothes'],
            'groups' => [],
            'family' => 'clothes',
            'values' => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'jacket',
                    ],
                ],
                'description' => [
                    [
                        'locale' => 'en_US',
                        'scope' => 'ecommerce',
                        'data' => 'A wonderful description...',
                    ],
                ],
                'media' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'a/b/c/123456_filename.jpg',
                    ],
                ],
            ],
        ];

        $attributeRepository->getAttributeTypeByCodes(['sku', 'description', 'media'])
            ->shouldBeCalled()
            ->willReturn([
                'sku' => 'pim_catalog_identifier',
                'description' => 'pim_catalog_textarea',
                'media' => 'pim_catalog_image',
            ]);

        $fileInfo->getOriginalFilename()->willReturn('the file name.jpg');
        $fileInfo->getKey()->willReturn('a/b/c/123456_filename.jpg');
        $fileInfo->getStorage()->willReturn('catalogStorage');
        $fileInfoRepository->findOneByIdentifier('a/b/c/123456_filename.jpg')->shouldBeCalled()->willReturn($fileInfo);
        $fileExporterPath->generate(
            [
                'locale' => null,
                'scope' => null,
                'data' => 'a/b/c/123456_filename.jpg',
            ],
            [
                'identifier' => 'jacket',
                'code' => 'media',
            ]
        )->shouldBeCalled()->willReturn('files/jacket/media/');

        $filesystemProvider->getFilesystem('catalogStorage')->willReturn($catalogFilesystem);
        $catalogFilesystem->fileExists('a/b/c/123456_filename.jpg')->shouldBeCalled()->willReturn(true);

        $productStandardWithMedia = \array_replace_recursive(
            $productStandard,
            [
                'values' => [
                    'media' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'files/jacket/media/the file name.jpg',
                        ],
                    ],
                ],
            ]
        );
        $flatProduct = [
            'identifier' => 'jacket',
            'enabled' => '1',
            'categories' => '2015_clothes,2016_clothes',
            'groups' => '',
            'family' => 'clothes',
            'description-en_US-ecommerce' => 'A wonderful description...',
            'media' => 'files/jacket/media/the file name.jpg',
        ];

        $arrayConverter->convert($productStandardWithMedia, [])->shouldBeCalled()->willReturn($flatProduct);
        $generateHeadersFromFamilyCodes->__invoke(["clothes"], "ecommerce", ["fr_FR", "en_US"])->shouldBeCalled()
            ->willReturn([]);

        $flatRowBuffer->write([$flatProduct], ['withHeader' => true])->shouldBeCalled();

        $this->initialize();
        $this->write([$productStandard]);

        $this->getWrittenFiles()->shouldBeLike(
            [
                WrittenFileInfo::fromFileStorage(
                    'a/b/c/123456_filename.jpg',
                    'catalogStorage',
                    'files/jacket/media/the file name.jpg'
                ),
            ]
        );
    }

    function it_adds_a_warning_if_a_media_file_is_not_found(
        ArrayConverterInterface $arrayConverter,
        AttributeRepositoryInterface $attributeRepository,
        FileExporterPathGeneratorInterface $fileExporterPath,
        FileInfoRepositoryInterface $fileInfoRepository,
        FilesystemProvider $filesystemProvider,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        FileInfoInterface $fileInfo,
        FilesystemOperator $catalogFilesystem
    ) {
        $jobParameters = new JobParameters(
            [
                'withHeader' => true,
                'storage' => ['type' => 'local', 'file_path' => $this->directory . '%job_label%_product.csv'],
                'with_media' => true,
                'with_uuid' => true,
                'filters' => ['structure' => ['locales' => ['fr_FR', 'en_US'], 'scope' => 'ecommerce']],
            ]
        );
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $productStandard = [
            'uuid' => '93700f68-51ec-464a-8c47-ca9ac92d0b5c',
            'identifier' => null,
            'values' => [
                'media' => [['locale' => null, 'scope' => null, 'data' => 'a/b/c/abc123_filename.png']],
            ],
        ];
        $attributeRepository->getAttributeTypeByCodes(['media'])
            ->shouldBeCalled()
            ->willReturn([
                'media' => 'pim_catalog_image',
            ]);

        $fileExporterPath->generate(
            [
                'locale' => null,
                'scope' => null,
                'data' => 'a/b/c/abc123_filename.png',
            ],
            [
                'identifier' => '93700f68-51ec-464a-8c47-ca9ac92d0b5c',
                'code' => 'media',
            ]
        )->shouldBeCalled()->willReturn('files/93700f68-51ec-464a-8c47-ca9ac92d0b5c/media/');

        $fileInfo->getOriginalFilename()->willReturn('filename.png');
        $fileInfo->getKey()->willReturn('a/b/c/abc123_filename.png');
        $fileInfo->getStorage()->willReturn('catalogStorage');

        $fileInfoRepository->findOneByIdentifier('a/b/c/abc123_filename.png')->shouldBeCalled()->willReturn($fileInfo);
        $filesystemProvider->getFilesystem('catalogStorage')->willReturn($catalogFilesystem);
        $catalogFilesystem->fileExists('a/b/c/abc123_filename.png')->shouldBeCalled()->willReturn(false);

        $stepExecution->addWarning(
            'The media has not been found or is not currently available',
            [],
            Argument::that(
                function ($argument): bool {
                    return $argument instanceof InvalidItemInterface && $argument->getInvalidData() == [
                        'from' => 'a/b/c/abc123_filename.png',
                        'to' => [
                            'filePath' => 'files/93700f68-51ec-464a-8c47-ca9ac92d0b5c/media',
                            'filename' => 'filename.png',
                        ],
                        'storage' => 'catalogStorage',
                    ];
                }
            )
        )->shouldBeCalled();

        // product standard has not been updated with the generated file path
        $arrayConverter->convert($productStandard, ['with_uuid' => true])->shouldBeCalled()->willReturn([
            'uuid' => '93700f68-51ec-464a-8c47-ca9ac92d0b5c',
            'media' => 'a/b/c/abc123_filename.png',
        ]);

        $this->initialize();
        $this->write([$productStandard]);
        $this->getWrittenFiles()->shouldReturn([]);
    }

    function it_does_not_resolve_media_paths_if_option_is_false(
        ArrayConverterInterface $arrayConverter,
        AttributeRepositoryInterface $attributeRepository,
        FileExporterPathGeneratorInterface $fileExporterPath,
        GenerateFlatHeadersFromFamilyCodesInterface $generateHeadersFromFamilyCodes,
        FlatItemBuffer $flatRowBuffer,
        FileInfoRepositoryInterface $fileInfoRepository,
        StepExecution $stepExecution,
        JobExecution $jobExecution
    ) {
        $jobParameters = new JobParameters(
            [
                'withHeader' => true,
                'storage' => ['type' => 'local', 'file_path' => $this->directory . '%job_label%_product.csv'],
                'with_media' => false,
                'with_label' => false,
                'with_uuid' => false,
                'filters' => ['structure' => ['locales' => ['fr_FR', 'en_US'], 'scope' => 'ecommerce']],
            ]
        );

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $productStandard = [
            'uuid' => '351922ae-64dc-4df2-8260-73d62ccc4bc6',
            'identifier' => 'jacket',
            'enabled' => true,
            'categories' => ['2015_clothes', '2016_clothes'],
            'groups' => [],
            'family' => 'clothes',
            'values' => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'jacket',
                    ],
                ],
                'description' => [
                    [
                        'locale' => 'en_US',
                        'scope' => 'ecommerce',
                        'data' => 'A wonderful description...',
                    ],
                ],
                'media' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'a/b/c/123456_filename.jpg',
                    ],
                ],
            ],
        ];

        $attributeRepository->getAttributeTypeByCodes(Argument::any())->shouldNotBeCalled();
        $fileExporterPath->generate(Argument::cetera())->shouldNotBeCalled();
        $fileInfoRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();
        $flatProduct = [
            'identifier' => 'jacket',
            'enabled' => '1',
            'categories' => '2015_clothes,2016_clothes',
            'groups' => '',
            'family' => 'clothes',
            'description-en_US-ecommerce' => 'A wonderful description...',
            'media' => 'a/b/c/123456_filename.jpg',
        ];

        $arrayConverter->convert($productStandard, ['with_uuid' => false])->shouldBeCalled()->willReturn($flatProduct);
        $generateHeadersFromFamilyCodes->__invoke(["clothes"], "ecommerce", ["fr_FR", "en_US"])->shouldBeCalled()
            ->willReturn([]);

        $flatRowBuffer->write([$flatProduct], ['withHeader' => true])->shouldBeCalled();

        $this->initialize();
        $this->write([$productStandard]);

        $this->getWrittenFiles()->shouldBe([]);
    }

    function it_writes_the_csv_file_without_headers(
        FlatItemBufferFlusher $flusher,
        FlatItemBuffer $flatRowBuffer,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        ExecutionContext $executionContext
    ) {
        $jobParameters = new JobParameters(
            [
                'delimiter' => ';',
                'enclosure' => '"',
                'storage' => ['type' => 'local', 'file_path' => $this->directory . '%job_label%_product.csv'],
                'with_label' => false,
            ]
        );

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobInstance->getLabel()->willReturn('CSV Product export');
        $jobExecution->getExecutionContext()->willReturn($executionContext);
        $executionContext->get(JobInterface::WORKING_DIRECTORY_PARAMETER)->willReturn($this->directory);

        $stepExecution->getJobParameters()->willReturn($jobParameters);

        $flusher->setStepExecution($stepExecution)->shouldBeCalled();
        $flusher->flush($flatRowBuffer, Argument::type('array'), Argument::type('string'), -1)
            ->shouldBeCalled()
            ->willReturn(
                [
                    $this->directory . 'CSV_Product_export_product.csv',
                ]
            );

        $this->initialize();
        $this->flush();

        $this->getWrittenFiles()->shouldBeLike(
            [
                WrittenFileInfo::fromLocalFile(
                    $this->directory . 'CSV_Product_export_product.csv',
                    'CSV_Product_export_product.csv'
                ),
            ]
        );
    }

    function it_writes_the_csv_file_with_headers(
        FlatItemBufferFlusher $flusher,
        GenerateFlatHeadersFromFamilyCodesInterface $generateHeadersFromFamilyCodes,
        FlatItemBuffer $flatRowBuffer,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        ExecutionContext $executionContext
    ) {
        $jobParameters = new JobParameters(
            [
                'delimiter' => ';',
                'enclosure' => '"',
                'storage' => ['type' => 'local', 'file_path' => $this->directory . '%job_label%_product.csv'],
                'withHeader' => true,
                'with_label' => false,
                'filters' => ['structure' => ['locales' => ['fr_FR', 'en_US'], 'scope' => 'ecommerce']],
            ]
        );
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobInstance->getLabel()->willReturn('CSV Product export');
        $jobExecution->getExecutionContext()->willReturn($executionContext);
        $executionContext->get(JobInterface::WORKING_DIRECTORY_PARAMETER)->willReturn($this->directory);

        $descHeader = new FlatFileHeader(
            "description",
            true,
            'ecommerce',
            true,
            ['fr_FR', 'en_US']
        );
        $nameHeader = new FlatFileHeader("name", true, "ecommerce");
        $brandHeader = new FlatFileHeader("brand");
        $generateHeadersFromFamilyCodes
            ->__invoke(["family_1", "family_2"], 'ecommerce', ['fr_FR', 'en_US'])
            ->willReturn([$descHeader, $nameHeader, $brandHeader]);

        $flusher->setStepExecution($stepExecution)->shouldBeCalled();
        $flusher->flush($flatRowBuffer, Argument::type('array'), Argument::type('string'), -1)
            ->shouldBeCalled()
            ->willReturn(
                [
                    $this->directory . 'CSV_Product_export_product.csv',
                ]
            );

        $this->initialize();
        $this->write(
            [
                [
                    'sku' => 'sku-01',
                    'family' => 'family_1',
                ],
                [
                    'sku' => 'sku-02',
                    'family' => 'family_2',
                ],
            ]
        );
        $this->flush();

        $this->getWrittenFiles()->shouldBeLike(
            [
                WrittenFileInfo::fromLocalFile(
                    $this->directory . 'CSV_Product_export_product.csv',
                    'CSV_Product_export_product.csv'
                ),
            ]
        );
    }

    function it_writes_the_csv_file_with_headers_and_selected_attributes(
        FlatItemBufferFlusher $flusher,
        GenerateFlatHeadersFromAttributeCodesInterface $generateHeadersFromAttributeCodes,
        FlatItemBuffer $flatRowBuffer,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        ExecutionContext $executionContext
    ) {
        $jobParameters = new JobParameters(
            [
                'delimiter' => ';',
                'enclosure' => '"',
                'storage' => ['type' => 'local', 'file_path' => $this->directory . '%job_label%_product.csv'],
                'withHeader' => true,
                'with_label' => false,
                'filters' => ['structure' => ['locales' => ['fr_FR', 'en_US'], 'scope' => 'ecommerce']],
                'selected_properties' => ['name', 'description'],
            ]
        );
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobInstance->getLabel()->willReturn('CSV Product export');
        $jobExecution->getExecutionContext()->willReturn($executionContext);
        $executionContext->get(JobInterface::WORKING_DIRECTORY_PARAMETER)->willReturn($this->directory);

        $descHeader = new FlatFileHeader(
            "description",
            true,
            'ecommerce',
            true,
            ['fr_FR', 'en_US']
        );
        $nameHeader = new FlatFileHeader("name", true, "ecommerce");
        $generateHeadersFromAttributeCodes
            ->__invoke(["name", "description"], 'ecommerce', ['fr_FR', 'en_US'])
            ->willReturn([$nameHeader, $descHeader]);

        $flusher->setStepExecution($stepExecution)->shouldBeCalled();
        $flusher->flush($flatRowBuffer, Argument::type('array'), Argument::type('string'), -1)
            ->shouldBeCalled()
            ->willReturn(
                [
                    $this->directory . 'CSV_Product_export_product.csv',
                ]
            );

        $this->initialize();
        $this->write(
            [
                [
                    'sku' => 'sku-01',
                    'family' => 'family_1',
                ],
                [
                    'sku' => 'sku-02',
                    'family' => 'family_2',
                ],
            ]
        );
        $this->flush();
    }

    function it_writes_the_csv_file_with_label(
        ArrayConverterInterface $arrayConverter,
        FlatTranslatorInterface $flatTranslator,
        FlatItemBuffer $flatRowBuffer,
        GenerateFlatHeadersFromFamilyCodesInterface $generateHeadersFromFamilyCodes,
        JobExecution $jobExecution,
        StepExecution $stepExecution,
        JobInstance $jobInstance,
        ExecutionContext $executionContext
    ) {
        $jobParameters = new JobParameters(
            [
                'storage' => ['type' => 'local', 'file_path' => $this->directory . '%job_label%_product.csv'],
                'withHeader' => true,
                'with_label' => true,
                'filters' => ['structure' => ['locales' => ['fr_FR', 'en_US'], 'scope' => 'ecommerce']],
                'file_locale' => 'fr_FR',
                'header_with_label' => true,
            ]
        );
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getExecutionContext()->willReturn($executionContext);
        $jobInstance->getLabel()->willReturn('CSV Product export');
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $executionContext->get(JobInterface::WORKING_DIRECTORY_PARAMETER)->willReturn($this->directory);

        $productStandard1 = [
            'identifier' => 'jackets',
            'enabled' => true,
            'categories' => ['2015_clothes', '2016_clothes'],
            'groups' => [],
            'family' => 'clothes',
            'values' => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'jackets',
                    ],
                ],
                'description' => [
                    [
                        'locale' => 'en_US',
                        'scope' => 'ecommerce',
                        'data' => 'A wonderful description...',
                    ],
                    [
                        'locale' => 'en_US',
                        'scope' => 'mobile',
                        'data' => 'Simple description',
                    ],
                    [
                        'locale' => 'fr_FR',
                        'scope' => 'ecommerce',
                        'data' => 'Une description merveilleuse...',
                    ],
                    [
                        'locale' => 'fr_FR',
                        'scope' => 'mobile',
                        'data' => 'Une simple description',
                    ],
                ],
            ],
        ];

        $productFlat1 = [
            'enabled' => '1',
            'categories' => '2015_clothes, 2016_clothes',
            'groups' => '',
            'family' => 'clothes',
            'sku' => 'jackets',
            'description-en_US-ecommerce' => 'A wonderful description...',
            'description-en_US-mobile' => 'Simple description',
            'description-fr_FR-ecommerce' => 'Une description merveilleuse...',
            'description-fr_FR-mobile' => 'Une simple description',
        ];
        $arrayConverter->convert($productStandard1, [])->willReturn($productFlat1);

        $productStandard2 = [
            'identifier' => 'sweaters',
            'name' => [
                'en_US' => 'Sweaters',
                'en_GB' => 'Chandails',
            ],
            'values' => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'sweaters',
                    ],
                ],
                'media' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'wrong/path',
                    ],
                ],
            ],
        ];
        $generateHeadersFromFamilyCodes->__invoke(["clothes"], 'ecommerce', ['fr_FR', 'en_US'])->willReturn([]);

        $productFlat2 = [
            'label-en_US' => 'Sweaters',
            'label-fr_FR' => 'Chandails',
            'sku' => 'sweaters',
            'media' => 'wrong/path',
        ];

        $arrayConverter->convert($productStandard2, [])->willReturn($productFlat2);
        $flatTranslator
            ->translate([$productFlat1, $productFlat2], 'fr_FR', 'ecommerce', true)
            ->shouldBeCalled()
            ->willReturn(
                [
                    [
                        'Activé' => 'Oui',
                        'Catégories' => 'Vêtements 2015, Vêtements 2016',
                        'Groupes' => '',
                        'Famille' => 'Vêtements',
                        'Sku' => 'jackets',
                        'Description (Anglais, ecommerce)' => 'A wonderful description...',
                        'Description (Anglais, mobile)' => 'Simple description',
                        'Description (Français, ecommerce)' => 'Une description merveilleuse...',
                        'Description (Francais, mobile)' => 'Une simple description',
                        'Média' => 'files/jackets/media/it\'s the filename.jpg',
                    ],
                    [
                        'Nom (Anglais)' => 'Sweaters',
                        'Nom (Français)' => 'Chandails',
                        'Sku' => 'sweaters',
                        'Média' => 'wrong/path',
                    ],
                ]
            );

        $flatRowBuffer->write(
            [
                [
                    'Activé' => 'Oui',
                    'Catégories' => 'Vêtements 2015, Vêtements 2016',
                    'Groupes' => '',
                    'Famille' => 'Vêtements',
                    'Sku' => 'jackets',
                    'Description (Anglais, ecommerce)' => 'A wonderful description...',
                    'Description (Anglais, mobile)' => 'Simple description',
                    'Description (Français, ecommerce)' => 'Une description merveilleuse...',
                    'Description (Francais, mobile)' => 'Une simple description',
                    'Média' => 'files/jackets/media/it\'s the filename.jpg',
                ],
                [
                    'Nom (Anglais)' => 'Sweaters',
                    'Nom (Français)' => 'Chandails',
                    'Sku' => 'sweaters',
                    'Média' => 'wrong/path',
                ],
            ],
            ["withHeader" => true]
        )->shouldBeCalled();
        $this->initialize();
        $this->write([$productStandard1, $productStandard2]);
    }
}
