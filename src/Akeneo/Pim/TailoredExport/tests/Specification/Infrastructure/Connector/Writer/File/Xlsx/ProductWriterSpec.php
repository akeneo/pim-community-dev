<?php

namespace Specification\Akeneo\Pim\TailoredExport\Infrastructure\Connector\Writer\File\Xlsx;

use Akeneo\Pim\TailoredExport\Infrastructure\Connector\Writer\File\Xlsx\ProductWriter;
use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Buffer\BufferFactory;
use Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBuffer;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBufferFlusher;
use Akeneo\Tool\Component\Connector\Writer\File\WrittenFileInfo;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Filesystem\Filesystem;

class ProductWriterSpec extends ObjectBehavior
{
    function let(
        BufferFactory $bufferFactory,
        FlatItemBufferFlusher $flusher,
        Filesystem $filesystem,
        FlatItemBuffer $flatRowBuffer,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobInstance $jobInstance
    ) {
        $bufferFactory->create()->willReturn($flatRowBuffer);

        $this->beConstructedWith(
            $bufferFactory,
            $flusher,
            $filesystem
        );

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobInstance->getLabel()->willReturn('XLSX Product export');
        $stepExecution->getStartTime()->willReturn(\DateTime::createFromFormat('Y-m-d H:i:s', '2021-03-24 16:00:00'));
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_file_writer()
    {
        $this->shouldImplement(ItemWriterInterface::class);
        $this->shouldImplement(StepExecutionAwareInterface::class);
        $this->shouldImplement(InitializableInterface::class);
        $this->shouldImplement(FlushableInterface::class);
        $this->shouldImplement(ArchivableWriterInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductWriter::class);
    }

    function it_generate_file_path_depending_on_step_execution_parameters(StepExecution $stepExecution)
    {
        $stepExecution->getJobParameters()->willReturn(new JobParameters(
            [
                'linesPerFile' => 10000,
                'filePath' => '/tmp/my_custom_export_product.xlsx',
                'withHeader' => false,
            ]
        ));

        $this->getPath()->shouldReturn('/tmp/my_custom_export_product.xlsx');

        $stepExecution->getJobParameters()->willReturn(new JobParameters(
            [
                'linesPerFile' => 10000,
                'filePath' => '/tmp/%job_label%_product.xlsx',
                'withHeader' => false,
            ]
        ));

        $this->getPath()->shouldReturn('/tmp/XLSX_Product_export_product.xlsx');

        $stepExecution->getJobParameters()->willReturn(new JobParameters(
            [
                'linesPerFile' => 10000,
                'filePath' => '/tmp/%job_label%%datetime%_product.xlsx',
                'withHeader' => false,
            ]
        ));

        $this->getPath()->shouldReturn('/tmp/XLSX_Product_export2021-03-24_16-00-00_product.xlsx');
    }
    function it_call_the_flusher_and_store_the_written_files(
        FlatItemBufferFlusher $flusher,
        FlatItemBuffer $flatRowBuffer,
        StepExecution $stepExecution
    ) {
        $jobParameters = new JobParameters(
            [
                'linesPerFile' => 10000,
                'filePath' => '/tmp/%job_label%_product.xlsx',
                'withHeader' => false,
            ]
        );

        $stepExecution->getJobParameters()->willReturn($jobParameters);

        $flusher->setStepExecution($stepExecution)->shouldBeCalled();
        $flatRowBuffer->write([['sku' => 42, 'name' => 'bag']], ['withHeader' => false]);
        $flusher->flush(
            $flatRowBuffer,
            ['type' => 'xlsx'],
            '/tmp/XLSX_Product_export_product.xlsx',
            10000
        )
            ->shouldBeCalled()
            ->willReturn(
                [
                    '/tmp/XLSX_Product_export_product1.xlsx',
                    '/tmp/XLSX_Product_export_product2.xlsx',
                ]
            );

        $this->initialize();
        $this->write([['sku' => 42, 'name' => 'bag']]);
        $this->flush();

        $this->getWrittenFiles()->shouldBeLike(
            [
                WrittenFileInfo::fromLocalFile(
                    '/tmp/XLSX_Product_export_product1.xlsx',
                    'XLSX_Product_export_product1.xlsx'
                ),
                WrittenFileInfo::fromLocalFile(
                    '/tmp/XLSX_Product_export_product2.xlsx',
                    'XLSX_Product_export_product2.xlsx'
                ),
            ]
        );
    }
}
