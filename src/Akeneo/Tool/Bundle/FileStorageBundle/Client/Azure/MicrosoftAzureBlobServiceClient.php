<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\FileStorageBundle\Client\Azure;

use Akeneo\Tool\Bundle\FileStorageBundle\Auth\StorageSharedKeyCredential;
use Akeneo\Tool\Bundle\FileStorageBundle\Helpers\Azure\ConnectionStringHelper;
use Psr\Http\Message\UriInterface;

final class MicrosoftAzureBlobServiceClient
{
    public function __construct(
        public UriInterface $uri,
        public readonly ?StorageSharedKeyCredential $sharedKeyCredentials = null,
    ) {
        // must always include the forward slash (/) to separate the host name from the path and query portions of the URI.
        $this->uri = $uri->withPath(rtrim($uri->getPath(), '/') . "/");
    }

    public static function fromConnectionString(string $connectionString): self
    {
        $uri = ConnectionStringHelper::getBlobEndpoint($connectionString);
        if ($uri === null) {
            throw new \Exception('Invalid connection string');
        }

        $sas = ConnectionStringHelper::getSas($connectionString);
        if ($sas !== null) {
            return new self($uri->withQuery($sas));
        }

        $accountName = ConnectionStringHelper::getAccountName($connectionString);
        $accountKey = ConnectionStringHelper::getAccountKey($connectionString);
        if ($accountName !== null && $accountKey !== null) {
            return new self($uri, new StorageSharedKeyCredential($accountName, $accountKey));
        }

        throw new \Exception('Invalid connection string');
    }

    public function getContainerClient(string $containerName): MicrosoftAzureBlobContainerClient
    {
        return new MicrosoftAzureBlobContainerClient(
            $this->uri->withPath($this->uri->getPath() . $containerName),
            $this->sharedKeyCredentials,
        );
    }
}
