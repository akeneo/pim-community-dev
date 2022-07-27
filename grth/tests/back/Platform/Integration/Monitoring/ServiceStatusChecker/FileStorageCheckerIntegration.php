<?php
declare(strict_types=1);

namespace AkeneoTestEnterprise\Platform\Integration\Monitoring\ServiceStatusChecker;

use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\FileStorageChecker;
use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\ServiceStatus;
use Akeneo\Test\Integration\TestCase;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToCopyFile;
use League\Flysystem\UnableToCreateDirectory;
use League\Flysystem\UnableToDeleteDirectory;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToMoveFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnableToSetVisibility;
use League\Flysystem\UnableToWriteFile;
use PHPUnit\Framework\Assert;

final class FileStorageCheckerIntegration extends TestCase
{
    public function test_filestorage_is_ok_when_when_you_can_create_a_directory_in_each_filestorage(): void
    {
        Assert::assertEquals(ServiceStatus::ok(), $this->getFilestorageChecker()->status());
    }

    public function test_filestorage_is_ko_when_when_you_cant_create_a_directory_in_at_least_one_of_the_filestorage(): void
    {
        self::getContainer()->set('oneup_flysystem.catalog_storage_filesystem', $this->nullFilesystem());

        Assert::assertEquals(
            ServiceStatus::notOk('Failing file storages: catalogStorage'),
            $this->getFilestorageChecker()->status()
        );
    }

    protected function getConfiguration()
    {
        return null;
    }

    private function getFilestorageChecker(): FileStorageChecker
    {
        return $this->get('Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\FileStorageChecker');
    }

    private function nullFilesystem(): FilesystemOperator
    {
        return new Filesystem(
            new class implements FilesystemAdapter {
                public function fileExists(string $path): bool
                {
                    return false;
                }
                public function write(string $path, string $contents, Config $config): void
                {
                    throw UnableToWriteFile::atLocation($path);
                }
                public function writeStream(string $path, $contents, Config $config): void
                {
                    throw UnableToWriteFile::atLocation($path);
                }
                public function read(string $path): string
                {
                    throw UnableToReadFile::fromLocation($path);
                }
                public function readStream(string $path)
                {
                    throw UnableToReadFile::fromLocation($path);
                }
                public function delete(string $path): void
                {
                    throw UnableToDeleteFile::atLocation($path);
                }
                public function deleteDirectory(string $path): void
                {
                    throw UnableToDeleteDirectory::atLocation($path);
                }
                public function createDirectory(string $path, Config $config): void
                {
                    throw UnableToCreateDirectory::atLocation($path);
                }
                public function setVisibility(string $path, string $visibility): void
                {
                    throw UnableToSetVisibility::atLocation($path);
                }
                public function visibility(string $path): FileAttributes
                {
                    throw UnableToRetrieveMetadata::visibility($path);
                }
                public function mimeType(string $path): FileAttributes
                {
                    throw UnableToRetrieveMetadata::mimeType($path);
                }
                public function lastModified(string $path): FileAttributes
                {
                    throw UnableToRetrieveMetadata::lastModified($path);
                }
                public function fileSize(string $path): FileAttributes
                {
                    throw UnableToRetrieveMetadata::fileSize($path);
                }
                public function listContents(string $path, bool $deep): iterable
                {
                    return [];
                }
                public function move(string $source, string $destination, Config $config): void
                {
                    throw UnableToMoveFile::fromLocationTo($source, $destination);
                }
                public function copy(string $source, string $destination, Config $config): void
                {
                    throw UnableToCopyFile::fromLocationTo($source, $destination);
                }
            }
        );
    }
}
