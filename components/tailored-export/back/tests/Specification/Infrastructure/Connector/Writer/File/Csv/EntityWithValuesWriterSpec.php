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

namespace Specification\Akeneo\Platform\TailoredExport\Infrastructure\Connector\Writer\File\Csv;

use Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor\ProcessedTailoredExport;
use Akeneo\Platform\TailoredExport\Infrastructure\Connector\Writer\File\Csv\EntityWithValuesWriter;
use Akeneo\Platform\TailoredExport\Infrastructure\Connector\Writer\File\FileWriterFactory;
use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\WrittenFileInfo;
use OpenSpout\Writer\Common\Creator\WriterEntityFactory;
use OpenSpout\Writer\WriterInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Filesystem\Filesystem;

class EntityWithValuesWriterSpec extends ObjectBehavior
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
        $jobInstance->getLabel()->willReturn('CSV Product export');
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
        $this->shouldHaveType(EntityWithValuesWriter::class);
    }

    public function it_generates_file_path_depending_on_step_execution_parameters_and_line_per_files(
        StepExecution $stepExecution
    ) {
        $stepExecution->getTotalItems()->willReturn(10);
        $stepExecution->getJobParameters()->willReturn(new JobParameters([
            'linesPerFile' => 10000,
            'storage' => [
                'type' => 'local',
                'file_path' => '/tmp/my_custom_export_product.csv',
            ],
            'withHeader' => false,
        ]));

        $this->getPath()->shouldReturn('/tmp/my_custom_export_product.csv');

        $stepExecution->getJobParameters()->willReturn(new JobParameters([
            'linesPerFile' => 10000,
            'storage' => [
                'type' => 'local',
                'file_path' => '/tmp/%job_label%_product.csv',
            ],
            'withHeader' => false,
        ]));

        $this->getPath()->shouldReturn('/tmp/CSV_Product_export_product.csv');

        $stepExecution->getJobParameters()->willReturn(new JobParameters([
            'linesPerFile' => 10000,
            'storage' => [
                'type' => 'local',
                'file_path' => '/tmp/%job_label%%datetime%_product.csv',
            ],
            'withHeader' => false,
        ]));

        $this->getPath()->shouldReturn('/tmp/CSV_Product_export2021-03-24_16-00-00_product.csv');

        $stepExecution->getTotalItems()->willReturn(100000);
        $stepExecution->getJobParameters()->willReturn(new JobParameters([
            'linesPerFile' => 10000,
            'storage' => [
                'type' => 'local',
                'file_path' => '/tmp/%job_label%%datetime%_product.csv',
            ],
            'withHeader' => false,
        ]));

        $this->getPath()->shouldReturn('/tmp/CSV_Product_export2021-03-24_16-00-00_product_1.csv');
    }

    public function it_writes_file_without_header(
        StepExecution $stepExecution,
        FileWriterFactory $fileWriterFactory,
        WriterInterface $writer
    ) {
        $stepExecution->getTotalItems()->willReturn(5);
        $jobParameters = new JobParameters([
            'linesPerFile' => 6,
            'storage' => [
                'type' => 'local',
                'file_path' => '/tmp/%job_label%_product.csv',
            ],
            'withHeader' => false,
        ]);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $stepExecution->incrementSummaryInfo('write', 5)->shouldBeCalled();
        $fileWriterFactory->build([
            'fieldEnclosure' => '"',
            'fieldDelimiter' => ';',
            'shouldAddBOM'   => false
        ])->willReturn($writer);
        $writer->openToFile('/tmp/CSV_Product_export_product.csv')->shouldBeCalled();
        $writer->addRow(WriterEntityFactory::createRowFromArray(['sku' => 42, 'name' => 'bag']))->shouldBeCalled();
        $writer->addRow(WriterEntityFactory::createRowFromArray(['sku' => 52, 'name' => 'sunglasses']))->shouldBeCalled();
        $writer->addRow(WriterEntityFactory::createRowFromArray(['sku' => 62, 'name' => 'cap']))->shouldBeCalled();
        $writer->addRow(WriterEntityFactory::createRowFromArray(['sku' => 72, 'name' => 'bob']))->shouldBeCalled();
        $writer->addRow(WriterEntityFactory::createRowFromArray(['sku' => 82, 'name' => 'hat']))->shouldBeCalled();
        $writer->close()->shouldBeCalled();

        $this->initialize();
        $this->write([
            new ProcessedTailoredExport(['sku' => 42, 'name' => 'bag'], []),
            new ProcessedTailoredExport(['sku' => 52, 'name' => 'sunglasses'], []),
            new ProcessedTailoredExport(['sku' => 62, 'name' => 'cap'], []),
            new ProcessedTailoredExport(['sku' => 72, 'name' => 'bob'], []),
            new ProcessedTailoredExport(['sku' => 82, 'name' => 'hat'], [])
        ]);

        $this->flush();

        $this->getWrittenFiles()->shouldBeLike([
            WrittenFileInfo::fromLocalFile(
                '/tmp/CSV_Product_export_product.csv',
                'CSV_Product_export_product.csv'
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
            'storage' => [
                'type' => 'local',
                'file_path' => '/tmp/%job_label%_product.csv',
            ],
            'withHeader' => true,
        ]);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $stepExecution->incrementSummaryInfo('write', 5)->shouldBeCalled();

        $fileWriterFactory->build([
            'fieldEnclosure' => '"',
            'fieldDelimiter' => ';',
            'shouldAddBOM'   => false
        ])->willReturn($writer);
        $writer->openToFile('/tmp/CSV_Product_export_product.csv')->shouldBeCalled();
        $writer->addRow(WriterEntityFactory::createRowFromArray(['sku', 'name']))->shouldBeCalled();
        $writer->addRow(WriterEntityFactory::createRowFromArray(['sku' => 42, 'name' => 'bag']))->shouldBeCalled();
        $writer->addRow(WriterEntityFactory::createRowFromArray(['sku' => 52, 'name' => 'sunglasses']))->shouldBeCalled();
        $writer->addRow(WriterEntityFactory::createRowFromArray(['sku' => 62, 'name' => 'cap']))->shouldBeCalled();
        $writer->addRow(WriterEntityFactory::createRowFromArray(['sku' => 72, 'name' => 'bob']))->shouldBeCalled();
        $writer->addRow(WriterEntityFactory::createRowFromArray(['sku' => 82, 'name' => 'hat']))->shouldBeCalled();
        $writer->close()->shouldBeCalled();

        $this->initialize();
        $this->write([
            new ProcessedTailoredExport(['sku' => 42, 'name' => 'bag'], []),
            new ProcessedTailoredExport(['sku' => 52, 'name' => 'sunglasses'], []),
            new ProcessedTailoredExport(['sku' => 62, 'name' => 'cap'], []),
            new ProcessedTailoredExport(['sku' => 72, 'name' => 'bob'], []),
            new ProcessedTailoredExport(['sku' => 82, 'name' => 'hat'], [])
        ]);

        $this->flush();

        $this->getWrittenFiles()->shouldBeLike([
            WrittenFileInfo::fromLocalFile(
                '/tmp/CSV_Product_export_product.csv',
                'CSV_Product_export_product.csv'
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
            'storage' => [
                'type' => 'local',
                'file_path' => '/tmp/%job_label%_product.csv',
            ],
            'withHeader' => false,
        ]);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $stepExecution->incrementSummaryInfo('write', 3)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('write', 2)->shouldBeCalled();
        $fileWriterFactory->build([
            'fieldEnclosure' => '"',
            'fieldDelimiter' => ';',
            'shouldAddBOM'   => false
        ])->willReturn($firstWriter, $secondWriter, $thirdWriter);
        $firstWriter->openToFile('/tmp/CSV_Product_export_product_1.csv')->shouldBeCalled();
        $firstWriter->addRow(WriterEntityFactory::createRowFromArray(['sku' => 42, 'name' => 'bag']))->shouldBeCalled();
        $firstWriter->addRow(WriterEntityFactory::createRowFromArray(['sku' => 52, 'name' => 'sunglasses']))->shouldBeCalled();
        $firstWriter->close()->shouldBeCalled();
        $secondWriter->openToFile('/tmp/CSV_Product_export_product_2.csv')->shouldBeCalled();
        $secondWriter->addRow(WriterEntityFactory::createRowFromArray(['sku' => 62, 'name' => 'cap']))->shouldBeCalled();
        $secondWriter->addRow(WriterEntityFactory::createRowFromArray(['sku' => 72, 'name' => 'bob']))->shouldBeCalled();
        $secondWriter->close()->shouldBeCalled();
        $thirdWriter->openToFile('/tmp/CSV_Product_export_product_3.csv')->shouldBeCalled();
        $thirdWriter->addRow(WriterEntityFactory::createRowFromArray(['sku' => 82, 'name' => 'hat']))->shouldBeCalled();
        $thirdWriter->close()->shouldBeCalled();

        $this->initialize();
        $this->write([
            new ProcessedTailoredExport(['sku' => 42, 'name' => 'bag'], []),
            new ProcessedTailoredExport(['sku' => 52, 'name' => 'sunglasses'], []),
            new ProcessedTailoredExport(['sku' => 62, 'name' => 'cap'], [])
        ]);
        $this->write([
            new ProcessedTailoredExport(['sku' => 72, 'name' => 'bob'], []),
            new ProcessedTailoredExport(['sku' => 82, 'name' => 'hat'], [])
        ]);
        $this->flush();

        $this->getWrittenFiles()->shouldBeLike([
            WrittenFileInfo::fromLocalFile(
                '/tmp/CSV_Product_export_product_1.csv',
                'CSV_Product_export_product_1.csv'
            ),
            WrittenFileInfo::fromLocalFile(
                '/tmp/CSV_Product_export_product_2.csv',
                'CSV_Product_export_product_2.csv'
            ),
            WrittenFileInfo::fromLocalFile(
                '/tmp/CSV_Product_export_product_3.csv',
                'CSV_Product_export_product_3.csv'
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
            'storage' => [
                'type' => 'local',
                'file_path' => '/tmp/%job_label%_product.csv',
            ],
            'withHeader' => false,
        ]);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $stepExecution->incrementSummaryInfo('write', 4)->shouldBeCalled();
        $fileWriterFactory->build([
            'fieldEnclosure' => '"',
            'fieldDelimiter' => ';',
            'shouldAddBOM'   => false
        ])->willReturn($firstWriter, $secondWriter);

        $firstWriter->openToFile('/tmp/CSV_Product_export_product_1.csv')->shouldBeCalled();
        $firstWriter->addRow(WriterEntityFactory::createRowFromArray(['sku' => 42, 'name' => 'bag']))->shouldBeCalled();
        $firstWriter->addRow(WriterEntityFactory::createRowFromArray(['sku' => 52, 'name' => 'sunglasses']))->shouldBeCalled();
        $firstWriter->close()->shouldBeCalled();

        $secondWriter->openToFile('/tmp/CSV_Product_export_product_2.csv')->shouldBeCalled();
        $secondWriter->addRow(WriterEntityFactory::createRowFromArray(['sku' => 62, 'name' => 'bob']))->shouldBeCalled();
        $secondWriter->addRow(WriterEntityFactory::createRowFromArray(['sku' => 72, 'name' => 'hat']))->shouldBeCalled();
        $secondWriter->close()->shouldBeCalled();

        $this->initialize();
        $this->write([
            new ProcessedTailoredExport(['sku' => 42, 'name' => 'bag'], []),
            new ProcessedTailoredExport(['sku' => 52, 'name' => 'sunglasses'], []),
            new ProcessedTailoredExport(['sku' => 62, 'name' => 'bob'], []),
            new ProcessedTailoredExport(['sku' => 72, 'name' => 'hat'], [])
        ]);
        $this->flush();

        $this->getWrittenFiles()->shouldBeLike([
            WrittenFileInfo::fromLocalFile(
                '/tmp/CSV_Product_export_product_1.csv',
                'CSV_Product_export_product_1.csv'
            ),
            WrittenFileInfo::fromLocalFile(
                '/tmp/CSV_Product_export_product_2.csv',
                'CSV_Product_export_product_2.csv'
            ),
        ]);
    }
}
