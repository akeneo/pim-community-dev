<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileImport\ServiceApi;

use Akeneo\Platform\Job\ServiceApi\JobInstance\File;
use Akeneo\Platform\Job\ServiceApi\JobInstance\LaunchJobInstanceCommand;
use Akeneo\Platform\Job\ServiceApi\JobInstance\LaunchJobInstanceHandlerInterface;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Exception\TailoredImportLaunchError;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\LaunchProductFileImport;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\LaunchProductFileImportResult;
use Psr\Log\LoggerInterface;

final class LaunchTailoredImport implements LaunchProductFileImport
{
    public function __construct(private readonly LaunchJobInstanceHandlerInterface $launchJobInstanceHandler, private LoggerInterface $logger)
    {
    }

    // @phpstan-ignore-next-line
    public function __invoke(string $productFileImportConfigurationCode, string $filename, $productFileResource): LaunchProductFileImportResult
    {
        $this->logger->info(
            'Attempt to Launch a Tailored Import',
            [
                'data' => [
                    'product_File_Import_Configuration_Code' => $productFileImportConfigurationCode,
                    'filename' => $filename,
                ],
            ],
        );
        try {
            $result = $this->launchJobInstanceHandler->handle(new LaunchJobInstanceCommand($productFileImportConfigurationCode, new File($filename, $productFileResource)));
        } catch (\Exception $e) {
            $this->logger->error(
                'Error while Launching a Tailored Import',
                [
                    'data' => [
                        'product_File_Import_Configuration_Code' => $productFileImportConfigurationCode,
                        'filename' => $filename,
                        'error' => $e->getMessage(),
                    ],
                ],
            );
            throw new TailoredImportLaunchError();
        }


        return new LaunchProductFileImportResult($result->jobExecutionId, $result->jobExecutionUrl);
    }
}
