<?php

namespace spec\Pim\Component\Connector\Writer\File\Csv;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\Writer\File\BulkFileExporter;
use Pim\Component\Connector\Writer\File\FilePathResolverInterface;
use Pim\Component\Connector\Writer\File\FlatItemBuffer;
use Akeneo\Component\Buffer\BufferFactory;
use Pim\Component\Connector\Writer\File\FlatItemBufferFlusher;
use Prophecy\Argument;

class VariantGroupWriterSpec extends ObjectBehavior
{
    function let(
        BufferFactory $bufferFactory,
        BulkFileExporter $fileExporter,
        FlatItemBufferFlusher $flusher
    ) {
        $this->beConstructedWith($bufferFactory, $fileExporter, $flusher);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Writer\File\Csv\VariantGroupWriter');
    }

    function it_prepares_the_export(
        $fileExporter,
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

        $variant1 = [
            'code'        => 'jackets',
            'axis'        => 'size,color',
            'type'        => 'variant',
            'label-en_US' => 'Jacket',
            'label-en_GB' => 'Jacket',
        ];
        $variant1Media = [
            'filePath'     => 'wrong/path',
            'exportPath'   => 'export',
            'storageAlias' => 'storageAlias',
        ];

        $variant2 = [
            'code'        => 'sweaters',
            'type'        => 'variant',
            'label-en_US' => 'Sweaters',
            'label-en_GB' => 'Chandails'
        ];
        $variant2Media = [
            'filePath'     => 'img/variant_group1.jpg',
            'exportPath'   => 'export',
            'storageAlias' => 'storageAlias',
        ];

        $items = [
            [
                'variant_group' => $variant1,
                'media' => $variant1Media,
            ],
            [
                'variant_group' => $variant2,
                'media' => $variant2Media
            ]
        ];

        $bufferFactory->create()->willReturn($flatRowBuffer);
        $flatRowBuffer->write([$variant1, $variant2], ['withHeader' => true])->shouldBeCalled();
        $fileExporter->exportAll([$variant1Media, $variant2Media], 'my/file')->shouldBeCalled();

        $fileExporter->getErrors()->willReturn([
            [
                'medium' => $variant1Media,
                'message' => 'Error message',
            ]
        ]);
        $fileExporter->getCopiedMedia()->willReturn([
            [
                'copyPath'       => '/tmp/export',
                'originalMedium' => $variant2Media
            ]
        ]);

        $stepExecution->addWarning(Argument::cetera())->shouldBeCalled();

        $this->initialize();
        $this->write($items);

        $this->getWrittenFiles()->shouldBeEqualTo([
            '/tmp/export' => 'export'
        ]);
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
            -1
        )->willReturn(['my/file/path/foo1', 'my/file/path/foo2']);

        $this->initialize();
        $this->flush();
    }
}
