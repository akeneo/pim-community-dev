<?php

namespace spec\Pim\Component\Connector\Writer\File\Csv;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\Writer\File\BulkFileExporter;
use Pim\Component\Connector\Writer\File\FilePathResolverInterface;
use Pim\Component\Connector\Writer\File\FlatItemBuffer;
use Pim\Component\Connector\Writer\File\FlatItemBufferFlusher;
use Akeneo\Component\Buffer\BufferFactory;
use Prophecy\Argument;

class ProductWriterSpec extends ObjectBehavior
{
    function let(
        BufferFactory $bufferFactory,
        BulkFileExporter $mediaCopier,
        FlatItemBufferFlusher $flusher
    ) {
        $this->beConstructedWith($bufferFactory, $mediaCopier, $flusher);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Writer\File\Csv\ProductWriter');
    }

    function it_prepares_the_export(
        $mediaCopier,
        $bufferFactory,
        FlatItemBuffer $flatRowBuffer,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('withHeader')->willReturn(true);
        $jobParameters->get('filePath')->willReturn('my/file/path');
        $jobParameters->has('mainContext')->willReturn(false);
        $jobParameters->has('with_media')->willReturn(true);
        $jobParameters->get('with_media')->willReturn(true);

        $bufferFactory->create()->willReturn($flatRowBuffer);

        $flatRowBuffer->write([
            [
                'id' => 123,
                'family' => 12,
            ],
            [
                'id' => 165,
                'family' => 45,
            ],
        ], ['withHeader' => true])->shouldBeCalled();

        $mediaCopier->exportAll([
            [
                'filePath' => null,
                'exportPath' => 'export',
                'storageAlias' => 'storageAlias',
            ],
            [
                'filePath' => 'img/product1.jpg',
                'exportPath' => 'export',
                'storageAlias' => 'storageAlias',
            ],
        ], 'my/file')->shouldBeCalled();

        $mediaCopier->getErrors()->willReturn([]);
        $mediaCopier->getCopiedMedia()->willReturn([
            [
                'copyPath'       => '/tmp/export',
                'originalMedium' => [
                    'filePath'     => 'img/product1.jpg',
                    'exportPath'   => 'export',
                    'storageAlias' => 'storageAlias',
                ]
            ]
        ]);

        $this->initialize();
        $this->write([
            [
                'product' => [
                    'id' => 123,
                    'family' => 12,
                ],
                'media' => [
                    'filePath' => null,
                    'exportPath' => 'export',
                    'storageAlias' => 'storageAlias',
                ],
            ],
            [
                'product' => [
                    'id' => 165,
                    'family' => 45,
                ],
                'media' => [
                    'filePath' => 'img/product1.jpg',
                    'exportPath' => 'export',
                    'storageAlias' => 'storageAlias',
                ],
            ]
        ]);

        $this->getWrittenFiles()->shouldBeEqualTo([
            '/tmp/export' => 'export'
        ]);
    }

    function it_does_not_copy_media_if_parameters_with_media_is_false(
        $bufferFactory,
        $mediaCopier,
        FlatItemBuffer $flatRowBuffer,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('withHeader')->willReturn(true);
        $jobParameters->get('filePath')->willReturn('my/file/path');
        $jobParameters->has('mainContext')->willReturn(false);
        $jobParameters->has('with_media')->willReturn(true);
        $jobParameters->get('with_media')->willReturn(false);

        $bufferFactory->create()->willReturn($flatRowBuffer);

        $flatRowBuffer->write([['id' => 123, 'family' => 12]], ['withHeader' => true])->shouldBeCalled();
        $mediaCopier->exportAll(Argument::cetera())->shouldNotBeCalled();

        $this->initialize();
        $this->write([
            [
                'product' => [
                    'id' => 123,
                    'family' => 12,
                ],
                'media' => [
                    'filePath' => null,
                    'exportPath' => 'export',
                    'storageAlias' => 'storageAlias',
                ],
            ]
        ]);

        $this->getWrittenFiles()->shouldBeEqualTo([]);
    }

    function it_writes_the_csv_file(
        $bufferFactory,
        $flusher,
        FlatItemBuffer $flatRowBuffer,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $this->setStepExecution($stepExecution);

        $flusher->setStepExecution($stepExecution)->shouldBeCalled();

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->has('linesPerFile')->willReturn(false);
        $jobParameters->get('delimiter')->willReturn(';');
        $jobParameters->get('enclosure')->willReturn('"');
        $jobParameters->get('filePath')->willReturn('my/file/path/foo');
        $jobParameters->has('mainContext')->willReturn(false);

        $bufferFactory->create()->willReturn($flatRowBuffer);

        $flusher->flush(
            $flatRowBuffer,
            Argument::type('array'),
            Argument::type('string'),
            -1,
            Argument::type('array')
        )->willReturn(['my/file/path/foo1', 'my/file/path/foo2']);

        $this->initialize();
        $this->flush();
    }
}
