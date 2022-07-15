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
use Akeneo\Tool\Component\Connector\Writer\File\FileExporterPathGeneratorInterface;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBuffer;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBufferFlusher;
use Akeneo\Tool\Component\Connector\Writer\File\WrittenFileInfo;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Akeneo\UserManagement\Component\Connector\Writer\File\XlsxUserWriter;
use League\Flysystem\FilesystemOperator;
use PhpSpec\ObjectBehavior;

class XlsxUserWriterSpec extends ObjectBehavior
{
    function let(
        ArrayConverterInterface $arrayConverter,
        BufferFactory $bufferFactory,
        FlatItemBufferFlusher $flusher,
        FileInfoRepositoryInterface $fileInfoRepository,
        FilesystemProvider $filesystemProvider,
        FileExporterPathGeneratorInterface $fileExporterPathGenerator,
        FlatItemBuffer $flatRowBuffer,
        StepExecution $stepExecution,
        ExecutionContext $executionContext,
        JobExecution $jobExecution,
        JobParameters $jobParameters
    ) {
        $this->beConstructedWith(
            $arrayConverter,
            $bufferFactory,
            $flusher,
            $fileInfoRepository,
            $filesystemProvider,
            $fileExporterPathGenerator
        );

        $executionContext->get(JobInterface::WORKING_DIRECTORY_PARAMETER)->willReturn('/tmp/akeneo_batch1234/');
        $jobExecution->getExecutionContext()->willReturn($executionContext);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobParameters->has('storage')->willReturn(true);
        $jobParameters->get('storage')->willReturn(['type' => 'local', 'file_path' => '/tmp/output_dir/users.xlsx']);
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
        FileInfoRepositoryInterface $fileInfoRepository,
        FilesystemProvider $filesystemProvider,
        FileExporterPathGeneratorInterface $fileExporterPathGenerator,
        FlatItemBuffer $flatRowBuffer,
        StepExecution $stepExecution,
        FileInfoInterface $fileInfo,
        FilesystemOperator $filesystem
    ) {
        $item = [
            'username' => 'julia',
            'enabled' => true,
            'avatar' => [
                'filePath' => 'a/b/c/abc_julia.png',
                'originalFilename' => 'julia.png',
            ],
        ];

        $fileInfo->getKey()->willReturn('a/b/c/abc_julia.png');
        $fileInfo->getStorage()->willReturn('catalogStorage');
        $fileInfo->getOriginalFilename()->willReturn('julia.png');
        $fileInfoRepository->findOneByIdentifier('a/b/c/abc_julia.png')->shouldBeCalled()->willReturn($fileInfo);

        $fileExporterPathGenerator->generate(
            ['locale' => null, 'scope' => null],
            ['code' => 'avatar', 'identifier' => 'julia']
        )->shouldBeCalled()->willReturn('files/julia/avatar/');
        $filesystemProvider->getFilesystem('catalogStorage')->willReturn($filesystem);
        $filesystem->fileExists('a/b/c/abc_julia.png')->shouldBeCalled()->willReturn(true);

        $flatItem = [
            'username' => 'julia',
            'enabled' => '1',
            'avatar' => 'files/julia/avatar/julia.png',
        ];
        $arrayConverter->convert(
            [
                'username' => 'julia',
                'enabled' => true,
                'avatar' => [
                    'filePath' => 'files/julia/avatar/julia.png',
                    'originalFilename' => 'julia.png',
                ],
            ],
            []
        )->shouldBeCalled()->willReturn($flatItem);
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
                WrittenFileInfo::fromFileStorage(
                    'a/b/c/abc_julia.png',
                    'catalogStorage',
                    'files/julia/avatar/julia.png'
                ),
                WrittenFileInfo::fromLocalFile('/tmp/output_dir/users.xlsx', 'users.xlsx'),
            ]
        );
    }
}
