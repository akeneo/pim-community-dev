<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\FileStorageBundle\Adapter;

use Akeneo\Tool\Bundle\FileStorageBundle\Client\Azure\MicrosoftAzureBlobContainerClient;
use Akeneo\Tool\Bundle\FileStorageBundle\Domain\Model\Azure\Blob\Blob;
use Akeneo\Tool\Bundle\FileStorageBundle\Domain\Model\Azure\Blob\BlobProperties;
use Akeneo\Tool\Bundle\FileStorageBundle\Domain\Model\Azure\Blob\GetBlobsOptions;
use Akeneo\Tool\Bundle\FileStorageBundle\Domain\Model\Azure\Blob\UploadBlobOptions;
use League\Flysystem\Config;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\PathPrefixer;
use League\Flysystem\UnableToCreateDirectory;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnableToSetVisibility;
use League\MimeTypeDetection\FinfoMimeTypeDetector;
use League\MimeTypeDetection\MimeTypeDetector;

final class MicrosoftAzureBlobStorageAdapter implements FilesystemAdapter
{
    private PathPrefixer $pathPrefixer;
    private MimeTypeDetector $mimeTypeDetector;

    public function __construct(
        private readonly MicrosoftAzureBlobContainerClient $azureBlobContainerClient,
        string $prefix = '',
        MimeTypeDetector $mimeTypeDetector = null,
    ) {
        $this->pathPrefixer = new PathPrefixer($prefix);
        $this->mimeTypeDetector = $mimeTypeDetector ?? new FinfoMimeTypeDetector();
    }

    public function fileExists(string $path): bool
    {
        return $this->azureBlobContainerClient
            ->getMicrosoftAzureBlobClient($this->pathPrefixer->prefixPath($path))
            ->exists();
    }

    public function directoryExists(string $path): bool
    {
        $options = new GetBlobsOptions(pageSize: 1);

        foreach (
            $this->azureBlobContainerClient->getBlobs(
                $this->pathPrefixer->prefixDirectoryPath($path),
                $options,
            ) as $ignored
        ) {
            return true;
        };

        return false;
    }

    public function write(string $path, string $contents, Config $config): void
    {
        $this->upload($path, $contents);
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        $this->upload($path, $contents);
    }

    private function upload(string $path, $contents): void
    {
        $path = $this->pathPrefixer->prefixPath($path);
        $mimetype = $this->mimeTypeDetector->detectMimetype($path, $contents);

        $options = new UploadBlobOptions(
            contentType: $mimetype,
        );

        $this->azureBlobContainerClient
            ->getMicrosoftAzureBlobClient($path)
            ->upload($contents, $options);
    }

    public function read(string $path): string
    {
        $result = $this->azureBlobContainerClient
            ->getMicrosoftAzureBlobClient($this->pathPrefixer->prefixPath($path))
            ->downloadStreaming();

        return $result->content->getContents();
    }

    public function readStream(string $path)
    {
        $result = $this->azureBlobContainerClient
            ->getMicrosoftAzureBlobClient($this->pathPrefixer->prefixPath($path))
            ->downloadStreaming();

        $resource = $result->content->detach();

        if ($resource === null) {
            throw new \Exception("Should not happen");
        }

        return $resource;
    }

    public function delete(string $path): void
    {
        $this->azureBlobContainerClient
            ->getMicrosoftAzureBlobClient($this->pathPrefixer->prefixPath($path))
            ->deleteIfExists();
    }

    public function deleteDirectory(string $path): void
    {
        foreach ($this->listContents($path, true) as $item) {
            if ($item instanceof FileAttributes) {
                $this->azureBlobContainerClient
                    ->getMicrosoftAzureBlobClient($this->pathPrefixer->prefixPath($item->path()))
                    ->delete();
            }
        }
    }

    public function createDirectory(string $path, Config $config): void
    {
        throw UnableToCreateDirectory::atLocation($path, 'Azure does not support this operation.');
    }

    public function setVisibility(string $path, string $visibility): void
    {
        throw UnableToSetVisibility::atLocation($path, 'Azure does not support this operation.');
    }

    public function visibility(string $path): FileAttributes
    {
        throw UnableToRetrieveMetadata::visibility($path, "Azure does not support this operation.");
    }

    public function mimeType(string $path): FileAttributes
    {
        return $this->fetchMetadata($path);
    }

    public function lastModified(string $path): FileAttributes
    {
        return $this->fetchMetadata($path);
    }

    public function fileSize(string $path): FileAttributes
    {
        return $this->fetchMetadata($path);
    }

    public function listContents(string $path, bool $deep): iterable
    {
        $prefix = $this->pathPrefixer->prefixDirectoryPath($path);

        if ($deep) {
            foreach ($this->azureBlobContainerClient->getBlobs($prefix) as $item) {
                yield $this->normalizeBlob($this->pathPrefixer->stripPrefix($item->name), $item->properties);
            }
        } else {
            foreach ($this->azureBlobContainerClient->getBlobsByHierarchy($prefix) as $item) {
                if ($item instanceof Blob) {
                    yield $this->normalizeBlob($this->pathPrefixer->stripPrefix($item->name), $item->properties);
                } else {
                    yield new DirectoryAttributes($this->pathPrefixer->stripPrefix($item->name));
                }
            }
        }
    }

    public function move(string $source, string $destination, Config $config): void
    {
        $this->copy($source, $destination, $config);
        $this->delete($source);
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        $sourceBlobClient = $this->azureBlobContainerClient->getMicrosoftAzureBlobClient($this->pathPrefixer->prefixPath($source));
        $targetBlobClient = $this->azureBlobContainerClient->getMicrosoftAzureBlobClient($this->pathPrefixer->prefixPath($destination));

        $targetBlobClient->copyFromUri($sourceBlobClient->uri);
    }

    private function fetchMetadata(string $path): FileAttributes
    {
        $path = $this->pathPrefixer->prefixPath($path);

        $properties = $this->azureBlobContainerClient
            ->getMicrosoftAzureBlobClient($path)
            ->getProperties();

        return $this->normalizeBlob($path, $properties);
    }

    private function normalizeBlob(string $name, BlobProperties $properties): FileAttributes
    {
        return new FileAttributes(
            $name,
            fileSize: $properties->contentLength,
            lastModified: $properties->lastModified->getTimestamp(),
            mimeType: $properties->contentType,
        );
    }
}
