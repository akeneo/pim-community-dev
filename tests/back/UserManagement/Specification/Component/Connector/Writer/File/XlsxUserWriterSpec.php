<?php

namespace Specification\Akeneo\UserManagement\Component\Connector\Writer\File;

use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Buffer\BufferFactory;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBuffer;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBufferFlusher;
use Akeneo\Tool\Component\Connector\Writer\File\WrittenFileInfo;
use Akeneo\UserManagement\Component\Connector\Writer\File\XlsxUserWriter;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Filesystem\Filesystem;

class XlsxUserWriterSpec extends ObjectBehavior
{
    function let(
        ArrayConverterInterface $arrayConverter,
        BufferFactory $bufferFactory,
        FlatItemBufferFlusher $flusher,
        Filesystem $localFs,
        FlatItemBuffer $flatRowBuffer,
        StepExecution $stepExecution,
        ExecutionContext $executionContext,
        JobExecution $jobExecution,
        JobParameters $jobParameters
    ) {
        $this->beConstructedWith($arrayConverter, $bufferFactory, $flusher, $localFs);

        $executionContext->get(JobInterface::WORKING_DIRECTORY_PARAMETER)->willReturn('/tmp/akeneo_batch1234/');
        $jobExecution->getExecutionContext()->willReturn($executionContext);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobParameters->get('filePath')->willReturn('/tmp/output_dir/users.xlsx');
        $jobParameters->get('withHeader')->willReturn(true);
        $jobParameters->has('linesPerFile')->willReturn(true);
        $jobParameters->get('linesPerFile')->willReturn(10000);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $bufferFactory->create()->willReturn($flatRowBuffer);
        $this->initialize();

        $this->setStepExecution($stepExecution);
    }

    function it_is_a_file_writer()
    {
        $this->shouldImplement(ItemWriterInterface::class);
        $this->shouldImplement(ArchivableWriterInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(XlsxUserWriter::class);
    }

    function it_writes_an_xlsx_file_and_exports_avatar_files(
        ArrayConverterInterface $arrayConverter,
        FlatItemBufferFlusher $flusher,
        Filesystem $localFs,
        FlatItemBuffer $flatRowBuffer,
        StepExecution $stepExecution
    ) {
        $item = [
            'username' => 'julia',
            'enabled' => true,
            'avatar' => [
                'filePath' => 'files/julia/avatar/julia.png',
                'originalFilename' => 'julia.png',
            ],
        ];
        $flatItem = [
            'username' => 'julia',
            'enabled' => '1',
            'avatar' => 'files/julia/avatar/julia.png',
        ];

        $localFs->mkdir('/tmp/output_dir')->shouldBeCalled();
        $arrayConverter->convert($item, [])->shouldBeCalled()->willReturn($flatItem);
        $localFs->exists('/tmp/akeneo_batch1234/files/julia/avatar/julia.png')->shouldBeCalled()->willReturn(true);
        $flatRowBuffer->write([$flatItem], ['withHeader' => true])->shouldBeCalled();

        $this->write([$item]);

        $flusher->setStepExecution($stepExecution)->shouldBeCalled();
        $flusher->flush(
            $flatRowBuffer,
            ['type' => 'xlsx'],
            '/tmp/output_dir/users.xlsx',
            10000,
        )->shouldBeCalled()->willReturn(['/tmp/output_dir/users.xlsx']);

        $this->flush();

        $this->getWrittenFiles()->shouldBeLike(
            [
                WrittenFileInfo::fromLocalFile(
                    '/tmp/akeneo_batch1234/files/julia/avatar/julia.png',
                    'files/julia/avatar/julia.png'
                ),
                WrittenFileInfo::fromLocalFile('/tmp/output_dir/users.xlsx', 'users.xlsx'),
            ]
        );
    }
}
