<?php

namespace spec\Pim\Component\Connector\Writer\File;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Buffer\BufferInterface;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\Writer\File\ColumnSorterInterface;
use Pim\Component\Connector\Writer\File\FilePathResolverInterface;
use Pim\Component\Connector\Writer\File\FlatItemBuffer;
use Pim\Component\Connector\Writer\File\BulkFileExporter;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;

class XlsxVariantGroupWriterSpec extends ObjectBehavior
{
    function let(
        FilePathResolverInterface $filePathResolver,
        FlatItemBuffer $flatRowBuffer,
        BulkFileExporter $mediaCopier,
        ColumnSorterInterface $columnSorter
    ) {
        $this->beConstructedWith($filePathResolver, $flatRowBuffer, $mediaCopier, $columnSorter, 10000);

        $filePathResolver->resolve(Argument::any(), Argument::type('array'))
            ->willReturn('/tmp/export/export.xlsx');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Writer\File\XlsxVariantGroupWriter');
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
        $flatRowBuffer,
        $mediaCopier,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('withHeader')->willReturn(true);
        $jobParameters->get('filePath')->willReturn(true);
        $jobParameters->has('mainContext')->willReturn(false);

        $items = [
            [
                'variant_group' => [
                    'code'        => 'jackets',
                    'axis'        => 'size,color',
                    'type'        => 'variant',
                    'label-en_US' => 'Jacket',
                    'label-en_GB' => 'Jacket'
                ],
                'media' => [
                    'filePath'     => 'wrong/path',
                    'exportPath'   => 'export',
                    'storageAlias' => 'storageAlias',
                ],
            ],
            [
                'variant_group' => [
                    'code'        => 'sweaters',
                    'type'        => 'variant',
                    'label-en_US' => 'Sweaters',
                    'label-en_GB' => 'Chandails'
                ],
                'media' => [
                    'filePath'     => 'img/variant_group1.jpg',
                    'exportPath'   => 'export',
                    'storageAlias' => 'storageAlias',
                ],
            ]
        ];

        $flatRowBuffer->write([
            [
                'code'        => 'jackets',
                'axis'        => 'size,color',
                'type'        => 'variant',
                'label-en_US' => 'Jacket',
                'label-en_GB' => 'Jacket'
            ],
            [
                'code'        => 'sweaters',
                'type'        => 'variant',
                'label-en_US' => 'Sweaters',
                'label-en_GB' => 'Chandails'
            ],
        ], true)->shouldBeCalled();

        $mediaCopier->exportAll([
            [
                'filePath'     => 'wrong/path',
                'exportPath'   => 'export',
                'storageAlias' => 'storageAlias',
            ],
            [
                'filePath'     => 'img/variant_group1.jpg',
                'exportPath'   => 'export',
                'storageAlias' => 'storageAlias',
            ],
        ], '/tmp/export')->shouldBeCalled();

        $mediaCopier->getErrors()->willReturn([
            [
                'medium' => [
                    'filePath'     => 'wrong/path',
                    'exportPath'   => 'export',
                    'storageAlias' => 'storageAlias',
                ],
                'message' => 'Error message',
            ]
        ]);
        $mediaCopier->getCopiedMedia()->willReturn([
            [
                'copyPath'       => '/tmp/export',
                'originalMedium' => [
                    'filePath'     => 'img/variant_group1.jpg',
                    'exportPath'   => 'export',
                    'storageAlias' => 'storageAlias',
                ]
            ]
        ]);

        $stepExecution->addWarning(Argument::cetera())->shouldBeCalled();

        $this->write($items);

        $this->getWrittenFiles()->shouldBeEqualTo([
            '/tmp/export' => 'export'
        ]);
    }

    function it_writes_the_xlsx_file(
        $flatRowBuffer,
        $columnSorter,
        StepExecution $stepExecution,
        BufferInterface $buffer,
        JobParameters $jobParameters
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('withHeader')->willReturn(true);
        $jobParameters->get('filePath')->willReturn(true);
        $jobParameters->has('mainContext')->willReturn(false);
        $jobParameters->get('linesPerFile')->willReturn(10000);

        $flatRowBuffer->count()->willReturn(100);
        $flatRowBuffer->getHeaders()->willReturn(['id', 'family']);
        $flatRowBuffer->getBuffer()->willReturn($buffer);
        $columnSorter->sort(['id','family'])->willReturn(['id','family']);
        $this->flush();
    }
}
