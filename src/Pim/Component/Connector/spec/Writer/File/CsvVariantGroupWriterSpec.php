<?php

namespace spec\Pim\Component\Connector\Writer\File;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArchiveDirectory;
use Pim\Component\Connector\Writer\File\BulkFileExporter;
use Pim\Component\Connector\Writer\File\ColumnSorterInterface;
use Pim\Component\Connector\Writer\File\FilePathResolverInterface;
use Pim\Component\Connector\Writer\File\FlatItemBuffer;
use Prophecy\Argument;

class CsvVariantGroupWriterSpec extends ObjectBehavior
{
    function let(
        FilePathResolverInterface $filePathResolver,
        ArchiveDirectory $archiveDirectory,
        FlatItemBuffer $flatRowBuffer,
        BulkFileExporter $fileExporter,
        ColumnSorterInterface $columnSorter
    ) {
        $this->beConstructedWith($filePathResolver, $archiveDirectory, $flatRowBuffer, $fileExporter, $columnSorter);

        $filePathResolver->resolve(Argument::any(), Argument::type('array'))
            ->willReturn('/tmp/export/export.csv');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Writer\File\CsvVariantGroupWriter');
    }

    function it_prepares_the_export(
        $flatRowBuffer,
        $fileExporter,
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

        $flatRowBuffer->write([$variant1, $variant2], true)->shouldBeCalled();
        $fileExporter->exportAll([$variant1Media, $variant2Media], '/tmp/export')->shouldBeCalled();

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

        $this->write($items);

        $this->getWrittenFiles()->shouldBeEqualTo([
            '/tmp/export' => 'export'
        ]);
    }
}
