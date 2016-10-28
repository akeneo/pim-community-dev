<?php

namespace spec\Pim\Component\Connector\Writer\File\Csv;

use Akeneo\Component\Batch\Item\ExecutionContext;
use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\Writer\File\FileExporterPathGeneratorInterface;
use Pim\Component\Connector\Writer\File\FlatItemBuffer;
use Akeneo\Component\Buffer\BufferFactory;
use Pim\Component\Connector\Writer\File\FlatItemBufferFlusher;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;

class VariantGroupWriterSpec extends ObjectBehavior
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

        $this->beConstructedWith($arrayConverter, $bufferFactory, $flusher, $attributeRepository, $fileExporterPath, [
            'pim_catalog_file', 'pim_catalog_image'
        ]);
    }

    function letGo()
    {
        $this->filesystem->remove($this->directory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Writer\File\Csv\VariantGroupWriter');
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
        $jobParameters->get('withHeader')->willReturn(true);
        $jobParameters->get('filePath')->willReturn($this->directory . 'variant_group.csv');
        $jobParameters->has('ui_locale')->willReturn(false);
        $jobParameters->has('decimalSeparator')->willReturn(false);
        $jobParameters->has('dateFormat')->willReturn(false);
        $jobParameters->has('with_media')->willReturn(true);
        $jobParameters->get('with_media')->willReturn(true);

        $variantStandard1 = [
            'code'   => 'jackets',
            'type'   => 'variant',
            'axis'   => ['size', 'color'],
            'labels' => [
                'en_US' => 'Jacket',
                'en_GB' => 'Jacket',
            ],
            'values' => [
                'media' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'  => 'files/jackets/media/it\'s the filename.jpg'
                    ]
                ]
            ]
        ];

        $variantFlat1 = [
            'code'        => 'jackets',
            'axis'        => 'size,color',
            'type'        => 'variant',
            'label-en_US' => 'Jacket',
            'label-en_GB' => 'Jacket',
            'media'       => 'files/jackets/media/it\'s the filename.jpg'
        ];

        $variantStandard2 = [
            'code'   => 'sweaters',
            'type'   => 'variant',
            'labels' => [
                'en_US' => 'Sweaters',
                'en_GB' => 'Chandails',
            ],
            'values' => [
                'media' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'wrong/path'
                    ]
                ]
            ]
        ];

        $variantFlat2 = [
            'code'        => 'sweaters',
            'type'        => 'variant',
            'label-en_US' => 'Sweaters',
            'label-en_GB' => 'Chandails',
            'media'       => 'wrong/path',
        ];


        $items = [$variantStandard1, $variantStandard2];

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(100);
        $jobInstance->getCode()->willReturn('csv_variant_group_export');

        $jobExecution->getExecutionContext()->willReturn($executionContext);
        $executionContext->get(JobInterface::WORKING_DIRECTORY_PARAMETER)->willReturn($this->directory);

        $variantPathMedia1 = $this->directory . 'files/jackets/media/';
        $originalFilename = "it's the filename.jpg";

        $this->filesystem->mkdir($variantPathMedia1);
        $this->filesystem->touch($variantPathMedia1 . $originalFilename);

        $bufferFactory->create()->willReturn($flatRowBuffer);

        $attributeRepository->getAttributeTypeByCodes(['media'])->willReturn(['media' => 'pim_catalog_image']);
        $fileExporterPath->generate($variantStandard1['values']['media'][0], [
            'identifier' => 'jackets', 'code' => 'media'
        ])->willReturn('files/jackets/media/');

        $fileExporterPath->generate($variantStandard2['values']['media'][0], [
            'identifier' => 'sweaters', 'code' => 'media'
        ])->willReturn('files/sweaters/media/');

        $variantStandard1['values']['media'][0]['data'] = 'files/jackets/media/' . $originalFilename;
        $arrayConverter->convert($variantStandard1, [])->willReturn($variantFlat1);
        $arrayConverter->convert($variantStandard2, [])->willReturn($variantFlat2);

        $flatRowBuffer->write([$variantFlat1, $variantFlat2], ['withHeader' => true])->shouldBeCalled();

        $this->initialize();
        $this->write($items);

        $this->getWrittenFiles()->shouldBeEqualTo([
            $variantPathMedia1 . 'it\'s the filename.jpg' => 'files/jackets/media/it\'s the filename.jpg'
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
        ExecutionContext $executionContext
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('withHeader')->willReturn(true);
        $jobParameters->get('filePath')->willReturn($this->directory . 'variant_group.csv');
        $jobParameters->has('ui_locale')->willReturn(false);
        $jobParameters->has('decimalSeparator')->willReturn(false);
        $jobParameters->has('dateFormat')->willReturn(false);
        $jobParameters->has('with_media')->willReturn(true);
        $jobParameters->get('with_media')->willReturn(false);

        $variantStandard1 = [
            'code'   => 'jackets',
            'type'   => 'variant',
            'axis'   => ['size', 'color'],
            'labels' => [
                'en_US' => 'Jacket',
                'en_GB' => 'Jacket',
            ],
            'values' => [
                'media' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data' => 'a/b/c/d/it_s_the_filename.jpg'
                    ]
                ]
            ]
        ];

        $variantFlat1 = [
            'code'        => 'jackets',
            'axis'        => 'size,color',
            'type'        => 'variant',
            'label-en_US' => 'Jacket',
            'label-en_GB' => 'Jacket',
            'media'       => 'files/jackets/media/it\'s the filename.jpg'
        ];

        $items = [$variantStandard1];

        $jobExecution->getExecutionContext()->willReturn($executionContext);
        $executionContext->get(JobInterface::WORKING_DIRECTORY_PARAMETER)->willReturn(null);

        $bufferFactory->create()->willReturn($flatRowBuffer);

        $attributeRepository->getAttributeTypeByCodes(['media'])->shouldNotBeCalled();
        $fileExporterPath->generate(Argument::cetera())->shouldNotBeCalled();

        $arrayConverter->convert($variantStandard1, [])->willReturn($variantFlat1);

        $flatRowBuffer->write([$variantFlat1], ['withHeader' => true])->shouldBeCalled();

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
        ExecutionContext $executionContext
    ) {
        $this->setStepExecution($stepExecution);

        $flusher->setStepExecution($stepExecution)->shouldBeCalled();

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getExecutionContext()->willReturn($executionContext);
        $executionContext->get(JobInterface::WORKING_DIRECTORY_PARAMETER)->willReturn($this->directory);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->has('linesPerFile')->willReturn(false);
        $jobParameters->get('delimiter')->willReturn(';');
        $jobParameters->get('enclosure')->willReturn('"');
        $jobParameters->get('filePath')->willReturn('my/file/path/foo');
        $jobParameters->has('ui_locale')->willReturn(false);

        $bufferFactory->create()->willReturn($flatRowBuffer);
        $flusher->flush(
            $flatRowBuffer,
            Argument::type('array'),
            Argument::type('string'),
            -1
        )->willReturn(['my/file/path/foo1', 'my/file/path/foo2']);

        $this->initialize();
        $this->flush();
    }
}
