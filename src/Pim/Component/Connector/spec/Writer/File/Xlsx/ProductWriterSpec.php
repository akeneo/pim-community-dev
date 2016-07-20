<?php

namespace spec\Pim\Component\Connector\Writer\File\Xlsx;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Buffer\BufferFactory;
use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\Writer\File\BulkFileExporter;
use Pim\Component\Connector\Writer\File\FilePathResolverInterface;
use Pim\Component\Connector\Writer\File\FlatItemBuffer;
use Pim\Component\Connector\Writer\File\FlatItemBufferFlusher;
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
        $this->shouldHaveType('Pim\Component\Connector\Writer\File\Xlsx\ProductWriter');
    }

    function it_is_a_configurable_step()
    {
        $this->shouldHaveType('Akeneo\Component\Batch\Item\AbstractConfigurableStepElement');
    }

    function it_is_a_writer()
    {
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemWriterInterface');
    }

    function it_prepares_the_export(
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
        $jobParameters->get('with_media')->willReturn(true);

        $items = $this->getItemToExport();

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
                'filePath' => 'wrong/path',
                'exportPath' => 'export',
                'storageAlias' => 'storageAlias',
            ],
            [
                'filePath' => 'img/product1.jpg',
                'exportPath' => 'export',
                'storageAlias' => 'storageAlias',
            ],
        ], 'my/file')->shouldBeCalled();

        $mediaCopier->getErrors()->willReturn([
            [
                'medium' => [
                    [
                        'filePath' => 'wrong/path',
                        'exportPath' => 'export',
                        'storageAlias' => 'storageAlias',
                    ]
                ],
                'message' => 'Error message',
            ]
        ]);
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

        $stepExecution->addWarning(Argument::cetera())->shouldBeCalled();
        $this->initialize();
        $this->write($items);

        $this->getWrittenFiles()->shouldBeEqualTo([
            '/tmp/export' => 'export'
        ]);
    }

    function it_writes_the_xlsx_file(
        $bufferFactory,
        $flusher,
        FlatItemBuffer $flatRowBuffer,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $this->setStepExecution($stepExecution);

        $flusher->setStepExecution($stepExecution)->shouldBeCalled();

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('linesPerFile')->willReturn(2);
        $jobParameters->get('filePath')->willReturn('my/file/path/foo');
        $jobParameters->has('mainContext')->willReturn(false);

        $bufferFactory->create()->willReturn($flatRowBuffer);

        $flusher->flush(
            $flatRowBuffer,
            Argument::type('array'),
            Argument::type('string'),
            2
        )->willReturn(['my/file/path/foo1', 'my/file/path/foo2']);

        $this->initialize();
        $this->flush();
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

    private function getItemToExport()
    {
        return [
            [
                'product' => [
                    'id' => 123,
                    'family' => 12,
                ],
                'media' => [
                    'filePath' => 'wrong/path',
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
        ];
    }
}
