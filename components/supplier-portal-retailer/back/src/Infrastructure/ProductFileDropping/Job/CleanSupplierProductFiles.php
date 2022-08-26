<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Job;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\DeleteProductFilesFromPaths;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\GoogleCloudStorage\DeleteUnknownSupplierDirectoriesInGCSBucket;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\DeleteOldProductFiles;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFilePathsOfOldProductFiles;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Psr\Log\LoggerInterface;

final class CleanSupplierProductFiles implements TaskletInterface
{
    private StepExecution $stepExecution;

    public function __construct(
        private GetProductFilePathsOfOldProductFiles $getProductFilePathsOfOldProductFiles,
        private DeleteProductFilesFromPaths $deleteProductFilesFromPaths,
        private DeleteOldProductFiles $deleteOldProductFilesInDatabase,
        private DeleteUnknownSupplierDirectoriesInGCSBucket $deleteUnknownSupplierDirectoriesInGCSBucket,
        private LoggerInterface $logger,
    ) {
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute(): void
    {
        try {
            $this->deleteOldProductFilesInGCSBucket();

            ($this->deleteOldProductFilesInDatabase)();

            ($this->deleteUnknownSupplierDirectoriesInGCSBucket)();
        } catch (\Exception $exception) {
            $this->stepExecution->addFailureException($exception);
            $this->logger->error('An error occurred during the cleaning of old supplier product files', [
                'data' => [
                    'step_execution_id' => $this->stepExecution->getId(),
                    'message' => $exception->getMessage(),
                ],
            ]);
        }
    }

    private function deleteOldProductFilesInGCSBucket(): void
    {
        $productFilesToDelete = ($this->getProductFilePathsOfOldProductFiles)();
        ($this->deleteProductFilesFromPaths)($productFilesToDelete);
    }
}
