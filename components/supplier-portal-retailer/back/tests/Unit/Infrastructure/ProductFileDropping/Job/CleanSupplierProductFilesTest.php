<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Infrastructure\ProductFileDropping\Job;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\DeleteProductFilesFromPaths;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFilePathsOfOldProductFiles;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\GoogleCloudStorage\DeleteUnknownSupplierDirectoriesInGCSBucket;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Job\CleanSupplierProductFiles;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use League\Flysystem\UnableToDeleteFile;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;

final class CleanSupplierProductFilesTest extends TestCase
{
    /** @test */
    public function itCleansTheProductFiles(): void
    {
        $getProductFilePathsOfOldProductFiles = $this->createMock(
            GetProductFilePathsOfOldProductFiles::class,
        );
        $deleteProductFilesFromPaths = $this->createMock(DeleteProductFilesFromPaths::class);
        $productFileRepository = $this->createMock(ProductFileRepository::class);
        $deleteUnknownSupplierDirectoriesInGCSBucket = $this->createMock(
            DeleteUnknownSupplierDirectoriesInGCSBucket::class,
        );

        $getProductFilePathsOfOldProductFiles
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn(['path/to/product_file_1.xlsx', 'path/to/product_file_2.xlsx'])
        ;

        $deleteProductFilesFromPaths
            ->expects($this->once())
            ->method('__invoke')
            ->with(['path/to/product_file_1.xlsx', 'path/to/product_file_2.xlsx'])
        ;

        $productFileRepository
            ->expects($this->once())
            ->method('deleteOldProductFiles')
        ;

        $deleteUnknownSupplierDirectoriesInGCSBucket
            ->expects($this->once())
            ->method('__invoke')
        ;

        $sut = new CleanSupplierProductFiles(
            $getProductFilePathsOfOldProductFiles,
            $deleteProductFilesFromPaths,
            $productFileRepository,
            $deleteUnknownSupplierDirectoriesInGCSBucket,
            new TestLogger(),
        );

        $sut->execute();
    }

    /** @test */
    public function itLogsAnErrorIfTheDeletionFailed(): void
    {
        $logger = new TestLogger();

        $getProductFilePathsOfOldProductFiles = $this->createMock(
            GetProductFilePathsOfOldProductFiles::class,
        );
        $deleteProductFilesFromPaths = $this->createMock(DeleteProductFilesFromPaths::class);
        $productFileRepository = $this->createMock(ProductFileRepository::class);
        $deleteUnknownSupplierDirectoriesInGCSBucket = $this->createMock(
            DeleteUnknownSupplierDirectoriesInGCSBucket::class,
        );
        $stepExecution = $this->createMock(StepExecution::class);

        $getProductFilePathsOfOldProductFiles
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn(['path/to/product_file_1.xlsx', 'path/to/product_file_2.xlsx'])
        ;

        $raisedException = new UnableToDeleteFile();

        $deleteProductFilesFromPaths
            ->expects($this->once())
            ->method('__invoke')
            ->with(['path/to/product_file_1.xlsx', 'path/to/product_file_2.xlsx'])
            ->willThrowException($raisedException)
        ;

        $stepExecution
            ->expects($this->once())
            ->method('addFailureException')
            ->with($raisedException)
        ;

        $sut = new CleanSupplierProductFiles(
            $getProductFilePathsOfOldProductFiles,
            $deleteProductFilesFromPaths,
            $productFileRepository,
            $deleteUnknownSupplierDirectoriesInGCSBucket,
            $logger,
        );

        $sut->setStepExecution($stepExecution);
        $sut->execute();

        static::assertTrue($logger->hasError([
            'message' => 'An error occurred during the cleaning of old supplier product files',
            'context' => [
                'data' => [
                    'step_execution_id' => null,
                    'message' => '',
                ],
            ],
        ]));
    }
}
