<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\Csv;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\Csv\ProductWriter;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\FlatFileHeader;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\GenerateFlatHeadersFromFamilyCodesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\GenerateFlatHeadersFromAttributeCodesInterface;
use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Buffer\BufferFactory;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\FileExporterPathGeneratorInterface;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBuffer;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBufferFlusher;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;

class ProductWriterSpec extends ObjectBehavior
{
    /** @var Filesystem */
    private $filesystem;

    /** @var string */
    private $directory;

    function let(
        ArrayConverterInterface $arrayConverter,
        BufferFactory $bufferFactory,
        FlatItemBufferFlusher $flusher,
        AttributeRepositoryInterface $attributeRepository,
        FileExporterPathGeneratorInterface $fileExporterPath,
        GenerateFlatHeadersFromFamilyCodesInterface $generateHeadersFromFamilyCodes,
        GenerateFlatHeadersFromAttributeCodesInterface $generateHeadersFromAttributeCodes
    ) {
        $this->directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'spec' . DIRECTORY_SEPARATOR;
        $this->filesystem = new Filesystem();
        $this->filesystem->mkdir($this->directory);

        $this->beConstructedWith(
            $arrayConverter,
            $bufferFactory,
            $flusher,
            $attributeRepository,
            $fileExporterPath,
            $generateHeadersFromFamilyCodes,
            $generateHeadersFromAttributeCodes,
            ['pim_catalog_file', 'pim_catalog_image']
        );
    }

    function letGo()
    {
        $this->filesystem->remove($this->directory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductWriter::class);
    }

    function it_prepares_the_export(
        $arrayConverter,
        $attributeRepository,
        $fileExporterPath,
        $bufferFactory,
        FlatItemBuffer $flatRowBuffer,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        ExecutionContext $executionContext
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getStartTime()->willReturn(new \DateTime());
        $jobParameters->get('withHeader')->willReturn(true);
        $jobParameters->get('filePath')->willReturn($this->directory . '%job_label%_product.csv');
        $jobParameters->has('ui_locale')->willReturn(false);
        $jobParameters->has('decimalSeparator')->willReturn(false);
        $jobParameters->has('dateFormat')->willReturn(false);
        $jobParameters->has('with_media')->willReturn(true);
        $jobParameters->get('with_media')->willReturn(true);

        $productStandard1 = [
            'identifier' => 'jackets',
            'enabled'    => true,
            'categories' => ['2015_clothes', '2016_clothes'],
            'groups'     => [],
            'family'     => 'clothes',
            'values'     => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'jackets',
                    ]
                ],
                'description' => [
                    [
                        'locale' => 'en_US',
                        'scope'  => 'ecommerce',
                        'data'   => 'A wonderful description...',
                    ],
                    [
                        'locale' => 'en_US',
                        'scope'  => 'mobile',
                        'data'   => 'Simple description',
                    ],
                    [
                        'locale' => 'fr_FR',
                        'scope'  => 'ecommerce',
                        'data'   => 'Une description merveilleuse...',
                    ],
                    [
                        'locale' => 'fr_FR',
                        'scope'  => 'mobile',
                        'data'   => 'Une simple description',
                    ],
                ],
                'media' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        // the file paths are resolved before the conversion to the standard format
                        'data'   => 'files/jackets/media/it\'s the filename.jpg',
                    ]
                ]
            ]
        ];

        $productFlat1 = [
            'enabled'                     => '1',
            'categories'                  => '2015_clothes, 2016_clothes',
            'groups'                      => '',
            'family'                      => 'clothes',
            'sku'                         => 'jackets',
            'description-en_US-ecommerce' => 'A wonderful description...',
            'description-en_US-mobile'    => 'Simple description',
            'description-fr_FR-ecommerce' => 'Une description merveilleuse...',
            'description-fr_FR-mobile'    => 'Une simple description',
            'media'                       => 'files/jackets/media/it\'s the filename.jpg',
        ];

        $productStandard2 = [
            'identifier' => 'sweaters',
            'type'   => 'product',
            'labels' => [
                'en_US' => 'Sweaters',
                'en_GB' => 'Chandails',
            ],
            'values' => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'sweaters'
                    ]
                ],
                'media' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'wrong/path',
                    ]
                ]
            ]
        ];

        $productFlat2 = [
            'type'        => 'product',
            'label-en_US' => 'Sweaters',
            'label-en_GB' => 'Chandails',
            'sku'         => 'sweaters',
            'media'       => 'wrong/path',
        ];

        $items = [$productStandard1, $productStandard2];

        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(100);
        $jobInstance->getCode()->willReturn('csv_product_export');
        $jobInstance->getLabel()->willReturn('CSV Product export');

        $jobExecution->getExecutionContext()->willReturn($executionContext);
        $executionContext->get(JobInterface::WORKING_DIRECTORY_PARAMETER)->willReturn($this->directory);

        $productPathMedia1 = $this->directory . 'files/jackets/media/';
        $originalFilename = "it's the filename.jpg";

        $this->filesystem->mkdir($productPathMedia1);
        $this->filesystem->touch($productPathMedia1 . $originalFilename);

        $bufferFactory->create()->willReturn($flatRowBuffer);

        $attributeRepository->getAttributeTypeByCodes(['sku', 'description', 'media'])
            ->willReturn(['media' => 'pim_catalog_image']);
        $attributeRepository->getAttributeTypeByCodes(['sku', 'media'])
            ->willReturn(['media' => 'pim_catalog_image']);

        $fileExporterPath->generate($productStandard1['values']['media'][0], [
            'identifier' => 'jackets', 'code' => 'media'
        ])->willReturn('files/jackets/media/');

        $fileExporterPath->generate($productStandard2['values']['media'][0], [
            'identifier' => 'sweaters', 'code' => 'media'
        ])->willReturn('files/sweaters/media/');

        $productStandard1['values']['media'][0]['data'] = 'files/jackets/media/' . $originalFilename;
        $arrayConverter->convert($productStandard1, [])->willReturn($productFlat1);
        $arrayConverter->convert($productStandard2, [])->willReturn($productFlat2);

        $flatRowBuffer->write([$productFlat1, $productFlat2], ['withHeader' => true])->shouldBeCalled();

        $this->initialize();
        $this->write($items);

        $this->getWrittenFiles()->shouldBeEqualTo([
            $productPathMedia1 . 'it\'s the filename.jpg' => 'files/jackets/media/it\'s the filename.jpg'
        ]);
    }

    function it_does_not_export_media_if_option_is_false(
        $arrayConverter,
        $attributeRepository,
        $fileExporterPath,
        $bufferFactory,
        FlatItemBuffer $flatRowBuffer,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        ExecutionContext $executionContext
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getStartTime()->willReturn(new \DateTime());
        $jobParameters->get('withHeader')->willReturn(true);
        $jobParameters->get('filePath')->willReturn($this->directory . '%job_label%_product.csv');
        $jobParameters->has('ui_locale')->willReturn(false);
        $jobParameters->has('decimalSeparator')->willReturn(false);
        $jobParameters->has('dateFormat')->willReturn(false);
        $jobParameters->has('with_media')->willReturn(true);
        $jobParameters->get('with_media')->willReturn(false);

        $productStandard = [
            'code'       => 'jackets',
            'enabled'    => true,
            'categories' => ['2015_clothes', '2016_clothes'],
            'groups'     => [],
            'family'     => 'clothes',
            'values'     => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'jackets'
                    ]
                ],
                'media' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        // the file paths are resolved before the conversion to the standard format
                        'data'   => 'files/jackets/media/it\'s the filename.jpg',
                    ]
                ]
            ]
        ];

        $productFlat1 = [
            'code'       => 'jackets',
            'enabled'    => '1',
            'categories' => '2015_clothes, 2016_clothes',
            'groups'     => '',
            'family'     => 'clothes',
            'sku--'      => 'jackets',
            'media--'    => 'a/b/c/d/it_s_the_filename.jpg',
        ];

        $items = [$productStandard];

        $jobExecution->getExecutionContext()->willReturn($executionContext);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobInstance->getLabel()->willReturn('CSV Product export');
        $executionContext->get(JobInterface::WORKING_DIRECTORY_PARAMETER)->willReturn($this->directory);

        $bufferFactory->create()->willReturn($flatRowBuffer);

        $attributeRepository->getAttributeTypeByCodes(['media'])->shouldNotBeCalled();
        $fileExporterPath->generate(Argument::cetera())->shouldNotBeCalled();

        $arrayConverter->convert($productStandard, [])->willReturn($productFlat1);

        $flatRowBuffer->write([$productFlat1], ['withHeader' => true])->shouldBeCalled();

        $this->initialize();
        $this->write($items);

        $this->getWrittenFiles()->shouldBeEqualTo([]);
    }

    function it_writes_the_csv_file_without_headers(
        $bufferFactory,
        $flusher,
        FlatItemBuffer $flatRowBuffer,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        ExecutionContext $executionContext
    ) {
        $this->setStepExecution($stepExecution);

        $flusher->setStepExecution($stepExecution)->shouldBeCalled();

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getStartTime()->willReturn(new \DateTime());
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobInstance->getLabel()->willReturn('CSV Product export');
        $jobExecution->getExecutionContext()->willReturn($executionContext);
        $executionContext->get(JobInterface::WORKING_DIRECTORY_PARAMETER)->willReturn($this->directory);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->has('linesPerFile')->willReturn(false);
        $jobParameters->get('delimiter')->willReturn(';');
        $jobParameters->get('enclosure')->willReturn('"');
        $jobParameters->get('filePath')->willReturn($this->directory . '%job_label%_product.csv');
        $jobParameters->has('ui_locale')->willReturn(false);
        $jobParameters->has('withHeader')->willReturn(false);

        $bufferFactory->create()->willReturn($flatRowBuffer);
        $flusher->flush(
            $flatRowBuffer,
            Argument::type('array'),
            Argument::type('string'),
            -1
        )->willReturn([
            $this->directory . 'CSV_Product_export_product1.csv',
            $this->directory . 'CSV_Product_export_product2.csv'
        ]);

        $this->initialize();
        $this->flush();
    }

    function it_writes_the_csv_file_with_headers(
        $bufferFactory,
        $flusher,
        $generateHeadersFromFamilyCodes,
        FlatItemBuffer $flatRowBuffer,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        ExecutionContext $executionContext
    ) {
        $this->setStepExecution($stepExecution);

        $flusher->setStepExecution($stepExecution)->shouldBeCalled();

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getStartTime()->willReturn(new \DateTime());
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobInstance->getLabel()->willReturn('CSV Product export');
        $jobExecution->getExecutionContext()->willReturn($executionContext);
        $executionContext->get(JobInterface::WORKING_DIRECTORY_PARAMETER)->willReturn($this->directory);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->has('linesPerFile')->willReturn(false);
        $jobParameters->get('delimiter')->willReturn(';');
        $jobParameters->get('enclosure')->willReturn('"');
        $jobParameters->get('filePath')->willReturn($this->directory . '%job_label%_product.csv');
        $jobParameters->has('ui_locale')->willReturn(false);
        $jobParameters->has('decimalSeparator')->willReturn(false);
        $jobParameters->has('dateFormat')->willReturn(false);
        $jobParameters->has('with_media')->willReturn(false);
        $jobParameters->has('selected_properties')->willReturn(false);
        $jobParameters->has('withHeader')->willReturn(true);
        $jobParameters->get('withHeader')->willReturn(true);
        $jobParameters->get('filters')->willReturn(['structure' => ['locales' => ['fr_FR', 'en_US'], 'scope' => 'ecommerce']]);

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


        $bufferFactory->create()->willReturn($flatRowBuffer);
        $flusher->flush(
            $flatRowBuffer,
            Argument::type('array'),
            Argument::type('string'),
            -1
        )->willReturn([
            $this->directory . 'CSV_Product_export_product1.csv',
            $this->directory . 'CSV_Product_export_product2.csv'
        ]);

        $this->initialize();
        $this->write([
            [
                'sku' => 'sku-01',
                'family' => 'family_1'
            ],
            [
                'sku' => 'sku-02',
                'family' => 'family_2'
            ]
        ]);
        $this->flush();
    }

    function it_writes_the_csv_file_with_headers_and_selected_attributes(
        $bufferFactory,
        $flusher,
        $generateHeadersFromAttributeCodes,
        FlatItemBuffer $flatRowBuffer,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        ExecutionContext $executionContext
    ) {
        $this->setStepExecution($stepExecution);

        $flusher->setStepExecution($stepExecution)->shouldBeCalled();

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getStartTime()->willReturn(new \DateTime());
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobInstance->getLabel()->willReturn('CSV Product export');
        $jobExecution->getExecutionContext()->willReturn($executionContext);
        $executionContext->get(JobInterface::WORKING_DIRECTORY_PARAMETER)->willReturn($this->directory);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->has('linesPerFile')->willReturn(false);
        $jobParameters->get('delimiter')->willReturn(';');
        $jobParameters->get('enclosure')->willReturn('"');
        $jobParameters->get('filePath')->willReturn($this->directory . '%job_label%_product.csv');
        $jobParameters->has('ui_locale')->willReturn(false);
        $jobParameters->has('decimalSeparator')->willReturn(false);
        $jobParameters->has('dateFormat')->willReturn(false);
        $jobParameters->has('with_media')->willReturn(false);
        $jobParameters->has('selected_properties')->willReturn(true);
        $jobParameters->get('selected_properties')->willReturn(['name', 'description']);
        $jobParameters->has('withHeader')->willReturn(true);
        $jobParameters->get('withHeader')->willReturn(true);
        $jobParameters->get('filters')->willReturn(['structure' => ['locales' => ['fr_FR', 'en_US'], 'scope' => 'ecommerce']]);

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

        $bufferFactory->create()->willReturn($flatRowBuffer);
        $flusher->flush(
            $flatRowBuffer,
            Argument::type('array'),
            Argument::type('string'),
            -1
        )->willReturn([
            $this->directory . 'CSV_Product_export_product1.csv',
            $this->directory . 'CSV_Product_export_product2.csv'
        ]);

        $this->initialize();
        $this->write([
            [
                'sku' => 'sku-01',
                'family' => 'family_1'
            ],
            [
                'sku' => 'sku-02',
                'family' => 'family_2'
            ]
        ]);
        $this->flush();
    }
}
