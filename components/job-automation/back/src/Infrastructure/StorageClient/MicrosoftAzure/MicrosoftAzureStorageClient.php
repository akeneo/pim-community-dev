<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\JobAutomation\Infrastructure\StorageClient\MicrosoftAzure;

use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient\FileSystemStorageClient;
use Akeneo\Platform\JobAutomation\Infrastructure\StorageClient\RemoteStorageClientInterface;
use League\Flysystem\FilesystemOperator;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;

final class MicrosoftAzureStorageClient extends FileSystemStorageClient implements RemoteStorageClientInterface
{
    public function __construct(
        private FilesystemOperator $filesystemOperator,
        private BlobRestProxy $azureBlobClient,
        private string $containerName,
    ) {
        parent::__construct($this->filesystemOperator);
    }

    public function isConnectionValid(): bool
    {
        try {
            $this->azureBlobClient->getContainerProperties($this->containerName);
        } catch (\Exception) {
            return false;
        }

        return true;
    }
}
