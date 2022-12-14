<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\JobAutomation\Infrastructure\StorageClient\MicrosoftAzure;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\StorageInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\StorageClientInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient\StorageClientProviderInterface;
use Akeneo\Platform\JobAutomation\Domain\Model\Storage\MicrosoftAzureStorage;
use Akeneo\Platform\JobAutomation\Infrastructure\Security\Encrypter;
use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;
use League\Flysystem\Filesystem;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;

final class MicrosoftAzureStorageClientProvider implements StorageClientProviderInterface
{
    public function __construct(
        private readonly Encrypter $encrypter,
    ) {
    }

    public function getFromStorage(StorageInterface $storage): StorageClientInterface
    {
        if (!$storage instanceof MicrosoftAzureStorage) {
            throw new \InvalidArgumentException('The provider only support MicrosoftAzureStorage');
        }

        $encryptionKey = $this->getEncryptionKey($storage);

        $azureClient = BlobRestProxy::createBlobService(
            $this->encrypter->decrypt($storage->getConnectionString(), $encryptionKey),
        );

        $azureAdapter = new AzureBlobStorageAdapter(
            $azureClient,
            $storage->getContainerName(),
        );

        return new MicrosoftAzureStorageClient(
            new Filesystem($azureAdapter),
            $azureClient,
            $storage->getContainerName(),
        );
    }

    public function supports(StorageInterface $storage): bool
    {
        return $storage instanceof MicrosoftAzureStorage;
    }

    private function getEncryptionKey(MicrosoftAzureStorage $storage): string
    {
        return $storage->getContainerName();
    }
}
