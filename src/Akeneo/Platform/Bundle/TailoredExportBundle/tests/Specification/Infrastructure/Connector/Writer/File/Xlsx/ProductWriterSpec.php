<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredExport\Infrastructure\Connector\Writer\File\Xlsx;

use Akeneo\Platform\TailoredExport\Infrastructure\Connector\Writer\File\FileWriterFactory;
use Akeneo\Platform\TailoredExport\Infrastructure\Connector\Writer\File\Xlsx\ProductWriter;
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
use Box\Spout\Writer\WriterInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Filesystem\Filesystem;

class ProductWriterSpec extends ObjectBehavior
{
    public function let(
        Filesystem $filesystem,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        FileWriterFactory $fileWriterFactory
    ) {
        $this->beConstructedWith($filesystem, $fileWriterFactory);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobInstance->getLabel()->willReturn('XLSX Product export');
        $stepExecution->getStartTime()->willReturn(\DateTime::createFromFormat('Y-m-d H:i:s', '2021-03-24 16:00:00'));
        $this->setStepExecution($stepExecution);
    }

    public function it_is_a_file_writer()
    {
        $this->shouldImplement(ItemWriterInterface::class);
        $this->shouldImplement(StepExecutionAwareInterface::class);
        $this->shouldImplement(InitializableInterface::class);
        $this->shouldImplement(FlushableInterface::class);
        $this->shouldImplement(ArchivableWriterInterface::class);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ProductWriter::class);
    }

    public function it_generates_file_path_depending_on_step_execution_parameters_and_line_per_files(
        StepExecution $stepExecution
    ) {
        $stepExecution->getTotalItems()->willReturn(10);
        $stepExecution->getJobParameters()->willReturn(new JobParameters([
            'linesPerFile' => 10000,
            'filePath' => '/tmp/my_custom_export_product.xlsx',
            'withHeader' => false,
        ]));

        $this->getPath()->shouldReturn('/tmp/my_custom_export_product.xlsx');

        $stepExecution->getJobParameters()->willReturn(new JobParameters([
            'linesPerFile' => 10000,
            'filePath' => '/tmp/%job_label%_product.xlsx',
            'withHeader' => false,
        ]));

        $this->getPath()->shouldReturn('/tmp/XLSX_Product_export_product.xlsx');

        $stepExecution->getJobParameters()->willReturn(new JobParameters([
            'linesPerFile' => 10000,
            'filePath' => '/tmp/%job_label%%datetime%_product.xlsx',
            'withHeader' => false,
        ]));

        $this->getPath()->shouldReturn('/tmp/XLSX_Product_export2021-03-24_16-00-00_product.xlsx');

        $stepExecution->getTotalItems()->willReturn(100000);
        $stepExecution->getJobParameters()->willReturn(new JobParameters([
            'linesPerFile' => 10000,
            'filePath' => '/tmp/%job_label%%datetime%_product.xlsx',
            'withHeader' => false,
        ]));

        $this->getPath()->shouldReturn('/tmp/XLSX_Product_export2021-03-24_16-00-00_product_1.xlsx');
    }

    public function it_writes_file_without_header(
        StepExecution $stepExecution,
        FileWriterFactory $fileWriterFactory,
        WriterInterface $writer
    ) {
        $stepExecution->getTotalItems()->willReturn(5);
        $jobParameters = new JobParameters([
            'linesPerFile' => 6,
            'filePath' => '/tmp/%job_label%_product.xlsx',
            'withHeader' => false,
        ]);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $stepExecution->incrementSummaryInfo("write", 5)->shouldBeCalled();
        $fileWriterFactory->build()->willReturn($writer);
        $writer->openToFile('/tmp/XLSX_Product_export_product.xlsx')->shouldBeCalled();
        $writer->addRow(['sku' => 42, 'name' => 'bag'])->shouldBeCalled();
        $writer->addRow(['sku' => 52, 'name' => 'sunglasses'])->shouldBeCalled();
        $writer->addRow(['sku' => 62, 'name' => 'cap'])->shouldBeCalled();
        $writer->addRow(['sku' => 72, 'name' => 'bob'])->shouldBeCalled();
        $writer->addRow(['sku' => 82, 'name' => 'hat'])->shouldBeCalled();
        $writer->close()->shouldBeCalled();

        $this->initialize();
        $this->write([
            ['sku' => 42, 'name' => 'bag'],
            ['sku' => 52, 'name' => 'sunglasses'],
            ['sku' => 62, 'name' => 'cap'],
            ['sku' => 72, 'name' => 'bob'],
            ['sku' => 82, 'name' => 'hat']
        ]);

        $this->flush();

        $this->getWrittenFiles()->shouldBeLike([
            WrittenFileInfo::fromLocalFile(
                '/tmp/XLSX_Product_export_product.xlsx',
                'XLSX_Product_export_product.xlsx'
            ),
        ]);
    }

    public function it_writes_file_with_header(
        StepExecution $stepExecution,
        FileWriterFactory $fileWriterFactory,
        WriterInterface $writer
    ) {
        $stepExecution->getTotalItems()->willReturn(5);
        $jobParameters = new JobParameters([
            'linesPerFile' => 6,
            'filePath' => '/tmp/%job_label%_product.xlsx',
            'withHeader' => true,
        ]);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $stepExecution->incrementSummaryInfo("write", 5)->shouldBeCalled();

        $fileWriterFactory->build()->willReturn($writer);
        $writer->openToFile('/tmp/XLSX_Product_export_product.xlsx')->shouldBeCalled();
        $writer->addRow(['sku', 'name'])->shouldBeCalled();
        $writer->addRow(['sku' => 42, 'name' => 'bag'])->shouldBeCalled();
        $writer->addRow(['sku' => 52, 'name' => 'sunglasses'])->shouldBeCalled();
        $writer->addRow(['sku' => 62, 'name' => 'cap'])->shouldBeCalled();
        $writer->addRow(['sku' => 72, 'name' => 'bob'])->shouldBeCalled();
        $writer->addRow(['sku' => 82, 'name' => 'hat'])->shouldBeCalled();
        $writer->close()->shouldBeCalled();

        $this->initialize();
        $this->write([
            ['sku' => 42, 'name' => 'bag'],
            ['sku' => 52, 'name' => 'sunglasses'],
            ['sku' => 62, 'name' => 'cap'],
            ['sku' => 72, 'name' => 'bob'],
            ['sku' => 82, 'name' => 'hat']
        ]);

        $this->flush();

        $this->getWrittenFiles()->shouldBeLike([
            WrittenFileInfo::fromLocalFile(
                '/tmp/XLSX_Product_export_product.xlsx',
                'XLSX_Product_export_product.xlsx'
            ),
        ]);
    }

    public function it_writes_several_files_when_lines_per_file_of_file_is_less_than_total_item(
        StepExecution $stepExecution,
        FileWriterFactory $fileWriterFactory,
        WriterInterface $firstWriter,
        WriterInterface $secondWriter,
        WriterInterface $thirdWriter
    ) {
        $stepExecution->getTotalItems()->willReturn(5);
        $jobParameters = new JobParameters([
            'linesPerFile' => 2,
            'filePath' => '/tmp/%job_label%_product.xlsx',
            'withHeader' => false,
        ]);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $stepExecution->incrementSummaryInfo("write", 3)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo("write", 2)->shouldBeCalled();
        $fileWriterFactory->build()->willReturn($firstWriter, $secondWriter, $thirdWriter);
        $firstWriter->openToFile('/tmp/XLSX_Product_export_product_1.xlsx')->shouldBeCalled();
        $firstWriter->addRow(['sku' => 42, 'name' => 'bag'])->shouldBeCalled();
        $firstWriter->addRow(['sku' => 52, 'name' => 'sunglasses'])->shouldBeCalled();
        $firstWriter->close()->shouldBeCalled();
        $secondWriter->openToFile('/tmp/XLSX_Product_export_product_2.xlsx')->shouldBeCalled();
        $secondWriter->addRow(['sku' => 62, 'name' => 'cap'])->shouldBeCalled();
        $secondWriter->addRow(['sku' => 72, 'name' => 'bob'])->shouldBeCalled();
        $secondWriter->close()->shouldBeCalled();
        $thirdWriter->openToFile('/tmp/XLSX_Product_export_product_3.xlsx')->shouldBeCalled();
        $thirdWriter->addRow(['sku' => 82, 'name' => 'hat'])->shouldBeCalled();
        $thirdWriter->close()->shouldBeCalled();

        $this->initialize();
        $this->write([
            ['sku' => 42, 'name' => 'bag'],
            ['sku' => 52, 'name' => 'sunglasses'],
            ['sku' => 62, 'name' => 'cap'],
        ]);
        $this->write([
            ['sku' => 72, 'name' => 'bob'],
            ['sku' => 82, 'name' => 'hat']
        ]);
        $this->flush();

        $this->getWrittenFiles()->shouldBeLike([
            WrittenFileInfo::fromLocalFile(
                '/tmp/XLSX_Product_export_product_1.xlsx',
                'XLSX_Product_export_product_1.xlsx'
            ),
            WrittenFileInfo::fromLocalFile(
                '/tmp/XLSX_Product_export_product_2.xlsx',
                'XLSX_Product_export_product_2.xlsx'
            ),
            WrittenFileInfo::fromLocalFile(
                '/tmp/XLSX_Product_export_product_3.xlsx',
                'XLSX_Product_export_product_3.xlsx'
            ),
        ]);
    }

    public function it_writes_several_files_with_headers(
        StepExecution $stepExecution,
        FileWriterFactory $fileWriterFactory,
        WriterInterface $firstWriter,
        WriterInterface $secondWriter
    ) {
        $stepExecution->getTotalItems()->willReturn(4);
        $jobParameters = new JobParameters([
            'linesPerFile' => 2,
            'filePath' => '/tmp/%job_label%_product.xlsx',
            'withHeader' => false,
        ]);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $stepExecution->incrementSummaryInfo("write", 4)->shouldBeCalled();
        $fileWriterFactory->build()->willReturn($firstWriter, $secondWriter);

        $firstWriter->openToFile('/tmp/XLSX_Product_export_product_1.xlsx')->shouldBeCalled();
        $firstWriter->addRow(['sku' => 42, 'name' => 'bag'])->shouldBeCalled();
        $firstWriter->addRow(['sku' => 52, 'name' => 'sunglasses'])->shouldBeCalled();
        $firstWriter->close()->shouldBeCalled();

        $secondWriter->openToFile('/tmp/XLSX_Product_export_product_2.xlsx')->shouldBeCalled();
        $secondWriter->addRow(['sku' => 62, 'name' => 'bob'])->shouldBeCalled();
        $secondWriter->addRow(['sku' => 72, 'name' => 'hat'])->shouldBeCalled();
        $secondWriter->close()->shouldBeCalled();

        $this->initialize();
        $this->write([
            ['sku' => 42, 'name' => 'bag'],
            ['sku' => 52, 'name' => 'sunglasses'],
            ['sku' => 62, 'name' => 'bob'],
            ['sku' => 72, 'name' => 'hat']
        ]);
        $this->flush();

        $this->getWrittenFiles()->shouldBeLike([
            WrittenFileInfo::fromLocalFile(
                '/tmp/XLSX_Product_export_product_1.xlsx',
                'XLSX_Product_export_product_1.xlsx'
            ),
            WrittenFileInfo::fromLocalFile(
                '/tmp/XLSX_Product_export_product_2.xlsx',
                'XLSX_Product_export_product_2.xlsx'
            ),
        ]);
    }
}
