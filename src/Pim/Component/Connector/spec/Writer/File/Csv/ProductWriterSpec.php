<?php

namespace spec\Pim\Component\Connector\Writer\File\Csv;

use Akeneo\Component\Batch\Item\ExecutionContext;
use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Buffer\BufferFactory;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\Writer\File\FileExporterPathGeneratorInterface;
use Pim\Component\Connector\Writer\File\FlatItemBuffer;
use Pim\Component\Connector\Writer\File\FlatItemBufferFlusher;
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
        FileExporterPathGeneratorInterface $fileExporterPath
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
            ['pim_catalog_file', 'pim_catalog_image']
        );
    }

    function letGo()
    {
        $this->filesystem->remove($this->directory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Writer\File\Csv\ProductWriter');
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

    function it_writes_the_csv_file(
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
}
