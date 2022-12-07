<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Spout;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\StreamStoredProductFile;
use OpenSpout\Reader\XLSX\Reader;

final class SpoutRemoteXlsxFileReaderFactory
{
    public function __construct(private readonly StreamStoredProductFile $streamStoredProductFile)
    {
    }

    public function create(string $remoteProductFilePath, string $remoteProductFileName): Reader
    {
        $localFilePath = $this->downloadRemoteFileToTemporaryFolder($remoteProductFilePath, $remoteProductFileName);

        $xlsxReader = new Reader();
        $xlsxReader->open($localFilePath);

        return $xlsxReader;
    }

    private function downloadRemoteFileToTemporaryFolder(string $remoteProductFilePath, string $remoteProductFileName): string
    {
        $localFilePath = \sprintf('%s%s%s', sys_get_temp_dir(), DIRECTORY_SEPARATOR, $remoteProductFileName);

        $productFileStream = ($this->streamStoredProductFile)($remoteProductFilePath);
        \file_put_contents($localFilePath, $productFileStream);

        return $localFilePath;
    }
}
