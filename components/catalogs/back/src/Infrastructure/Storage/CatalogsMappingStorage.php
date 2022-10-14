<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Storage;

use Akeneo\Catalogs\Application\Storage\CatalogsMappingStorageInterface;
use Akeneo\Catalogs\Infrastructure\Exception\FilesystemException;
use League\Flysystem\Filesystem;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CatalogsMappingStorage implements CatalogsMappingStorageInterface
{
    public function __construct(
        private Filesystem $filesystem,
    ) {
    }

    /**
     * @throws FilesystemException
     */
    public function exists(string $location): bool
    {
        try {
            return $this->filesystem->fileExists($location);
        } catch (\Throwable $e) {
            throw new FilesystemException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @return resource
     *
     * @throws FilesystemException
     */
    public function read(string $location)
    {
        try {
            return $this->filesystem->readStream($location);
        } catch (\Throwable $e) {
            throw new FilesystemException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws FilesystemException
     */
    public function write(string $location, string $contents): void
    {
        try {
            $this->filesystem->write($location, $contents);
        } catch (\Throwable $e) {
            throw new FilesystemException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws FilesystemException
     */
    public function delete(string $location): void
    {
        try {
            $this->filesystem->delete($location);
        } catch (\Throwable $e) {
            throw new FilesystemException($e->getMessage(), 0, $e);
        }
    }
}
