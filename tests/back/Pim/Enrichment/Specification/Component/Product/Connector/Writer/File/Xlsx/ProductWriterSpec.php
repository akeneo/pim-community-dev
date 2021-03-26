<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\Xlsx;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatTranslatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\FlatFileHeader;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\GenerateFlatHeadersFromAttributeCodesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\GenerateFlatHeadersFromFamilyCodesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\Xlsx\ProductWriter;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Platform\VersionProviderInterface;
use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Buffer\BufferFactory;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\FileExporterPathGeneratorInterface;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBuffer;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBufferFlusher;
use Akeneo\Tool\Component\Connector\Writer\File\WrittenFileInfo;
use Akeneo\Tool\Component\FileStorage\File\FileFetcherInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;

class ProductWriterSpec extends ObjectBehavior
{
    private Filesystem $filesystem;
    private string $directory;

    function let(
        ArrayConverterInterface $arrayConverter,
        BufferFactory $bufferFactory,
        FlatItemBufferFlusher $flusher,
        AttributeRepositoryInterface $attributeRepository,
        FileExporterPathGeneratorInterface $fileExporterPath,
        GenerateFlatHeadersFromFamilyCodesInterface $generateHeadersFromFamilyCodes,
        GenerateFlatHeadersFromAttributeCodesInterface $generateHeadersFromAttributeCodes,
        FlatTranslatorInterface $flatTranslator,
        FileInfoRepositoryInterface $fileInfoRepository,
        FilesystemProvider $filesystemProvider,
        FileFetcherInterface $fileFetcher,
        VersionProviderInterface $versionProvider,
        FlatItemBuffer $flatRowBuffer,
        StepExecution $stepExecution
    ) {
        $this->directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'spec' . DIRECTORY_SEPARATOR;
        $this->filesystem = new Filesystem();
        $this->filesystem->mkdir($this->directory);

        $bufferFactory->create()->willReturn($flatRowBuffer);

        $this->beConstructedWith(
            $arrayConverter,
            $bufferFactory,
            $flusher,
            $attributeRepository,
            $fileExporterPath,
            $generateHeadersFromFamilyCodes,
            $generateHeadersFromAttributeCodes,
            $flatTranslator,
            $fileInfoRepository,
            $filesystemProvider,
            $fileFetcher,
            $versionProvider,
            ['pim_catalog_file', 'pim_catalog_image']
        );

        $stepExecution->getStartTime()->willReturn(\DateTime::createFromFormat('Y-m-d H:i:s', '2021-03-24 16:00:00'));
        $this->setStepExecution($stepExecution);
    }

    function letGo()
    {
        $this->filesystem->remove($this->directory);
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

    function it_writes_the_xlsx_file_without_headers(
        FlatItemBufferFlusher $flusher,
        FlatItemBuffer $flatRowBuffer,
        VersionProviderInterface $versionProvider,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        ExecutionContext $executionContext
    ) {
        $jobParameters = new JobParameters(
            [
                'linesPerFile' => 10000,
                'filePath' => $this->directory . '%job_label%_product.xlsx',
                'with_label' => false,
                'withHeader' => false,
                'filters' => ['structure' => ['locales' => ['fr_FR', 'en_US'], 'scope' => 'ecommerce']],
            ]
        );

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobInstance->getLabel()->willReturn('XLSX Product export');
        $jobExecution->getExecutionContext()->willReturn($executionContext);
        $executionContext->get(JobInterface::WORKING_DIRECTORY_PARAMETER)->willReturn($this->directory);

        $stepExecution->getJobParameters()->willReturn($jobParameters);

        $flusher->setStepExecution($stepExecution)->shouldBeCalled();
        $flusher->flush(
            $flatRowBuffer,
            ['type' => 'xlsx'],
            $this->directory . 'XLSX_Product_export_product.xlsx',
            10000
        )
                ->shouldBeCalled()
                ->willReturn(
                    [
                        $this->directory . 'XLSX_Product_export_product1.xlsx',
                        $this->directory . 'XLSX_Product_export_product2.xlsx',
                    ]
                );
        $versionProvider->isSaaSVersion()->shouldBeCalled()->willReturn(false);

        $this->initialize();
        $this->flush();

        $this->getWrittenFiles()->shouldBeLike(
            [
                WrittenFileInfo::fromLocalFile(
                    $this->directory . 'XLSX_Product_export_product1.xlsx',
                    'XLSX_Product_export_product1.xlsx'
                ),
                WrittenFileInfo::fromLocalFile(
                    $this->directory . 'XLSX_Product_export_product2.xlsx',
                    'XLSX_Product_export_product2.xlsx'
                ),
            ]
        );
    }

    function it_writes_the_xlsx_file_with_headers(
        FlatItemBufferFlusher $flusher,
        GenerateFlatHeadersFromFamilyCodesInterface $generateHeadersFromFamilyCodes,
        FlatItemBuffer $flatRowBuffer,
        VersionProviderInterface $versionProvider,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        ExecutionContext $executionContext
    ) {
        $jobParameters = new JobParameters(
            [
                'linesPerFile' => 10000,
                'filePath' => $this->directory . '%job_label%_product.xlsx',
                'with_label' => false,
                'withHeader' => true,
                'filters' => ['structure' => ['locales' => ['fr_FR', 'en_US'], 'scope' => 'ecommerce']],
            ]
        );
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobInstance->getLabel()->willReturn('XLSX Product export');
        $jobExecution->getExecutionContext()->willReturn($executionContext);
        $executionContext->get(JobInterface::WORKING_DIRECTORY_PARAMETER)->willReturn($this->directory);

        $descHeader = new FlatFileHeader(
            "description",
            true,
            'ecommerce',
            true,
            ['fr_FR', 'en_US']
        );
        $nameHeader = new FlatFileHeader("name", true, "ecommerce");
        $brandHeader = new FlatFileHeader("brand");
        $generateHeadersFromFamilyCodes
            ->__invoke(["family_1", "family_2"], 'ecommerce', ['fr_FR', 'en_US'])
            ->willReturn([$descHeader, $nameHeader, $brandHeader]);

        $flusher->setStepExecution($stepExecution)->shouldBeCalled();
        $flusher->flush(
            $flatRowBuffer,
            ['type' => 'xlsx'],
            $this->directory . 'XLSX_Product_export_product.xlsx',
            10000
        )->shouldBeCalled()->willReturn(
            [
                $this->directory . 'XLSX_Product_export_product1.xlsx',
                $this->directory . 'XLSX_Product_export_product2.xlsx',
            ]
        );
        $versionProvider->isSaaSVersion()->shouldBeCalled()->willReturn(false);

        $this->initialize();
        $this->write(
            [
                [
                    'sku' => 'sku-01',
                    'family' => 'family_1',
                ],
                [
                    'sku' => 'sku-02',
                    'family' => 'family_2',
                ],
            ]
        );
        $this->flush();

        $this->getWrittenFiles()->shouldBeLike(
            [
                WrittenFileInfo::fromLocalFile(
                    $this->directory . 'XLSX_Product_export_product1.xlsx',
                    'XLSX_Product_export_product1.xlsx'
                ),
                WrittenFileInfo::fromLocalFile(
                    $this->directory . 'XLSX_Product_export_product2.xlsx',
                    'XLSX_Product_export_product2.xlsx'
                ),
            ]
        );
    }
}
