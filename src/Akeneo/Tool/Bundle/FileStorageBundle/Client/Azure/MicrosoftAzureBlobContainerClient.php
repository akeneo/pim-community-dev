<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\FileStorageBundle\Client\Azure;

use Akeneo\Tool\Bundle\FileStorageBundle\Auth\StorageSharedKeyCredential;
use Akeneo\Tool\Bundle\FileStorageBundle\Domain\Model\Azure\Blob\Blob;
use Akeneo\Tool\Bundle\FileStorageBundle\Domain\Model\Azure\Blob\BlobContainerProperties;
use Akeneo\Tool\Bundle\FileStorageBundle\Domain\Model\Azure\Blob\GetBlobsOptions;
use Akeneo\Tool\Bundle\FileStorageBundle\Domain\Model\Azure\Blob\Response\ListBlobsResponseBody;
use Akeneo\Tool\Bundle\FileStorageBundle\Middleware\ClientFactory;
use GuzzleHttp\Client;
use Psr\Http\Message\UriInterface;

final class MicrosoftAzureBlobContainerClient
{
    private Client $client;

    public function __construct(
        public readonly UriInterface $uri,
        public readonly ?StorageSharedKeyCredential $sharedKeyCredentials = null,
    ) {
        $this->setClient((new ClientFactory())->create($uri, $sharedKeyCredentials));
    }

    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    public function getMicrosoftAzureBlobClient(string $blobName): MicrosoftAzureBlobClient
    {
        return new MicrosoftAzureBlobClient(
            $this->uri->withPath($this->uri->getPath() . "/" . $blobName),
            $this->sharedKeyCredentials,
        );
    }

    public function getProperties(): BlobContainerProperties
    {
        $response = $this->client->request('GET', $this->uri, [
            'query' => [
                'restype' => 'container',
            ],
        ]);

        return BlobContainerProperties::fromResponseHeaders($response);
    }

    /**
     * @return \Generator<Blob>
     */
    public function getBlobs(?string $prefix = null, GetBlobsOptions $options = null): \Generator
    {
        $nextMarker = "";

        while (true) {
            $response = $this->listBlobs($prefix, null, $nextMarker, $options->pageSize);
            $nextMarker = $response->nextMarker;

            foreach ($response->blobs as $blob) {
                yield $blob;
            }

            if ($nextMarker === "") {
                break;
            }
        }
    }

    public function getBlobsByHierarchy(?string $prefix = null, string $delimiter = "/", GetBlobsOptions $options = null): \Generator
    {
        $nextMarker = "";

        while (true) {
            $response = $this->listBlobs($prefix, $delimiter, $nextMarker, $options->pageSize);
            $nextMarker = $response->nextMarker;

            foreach ($response->blobs as $blob) {
                yield $blob;
            }

            foreach ($response->blobPrefixes as $blobPrefix) {
                yield $blobPrefix;
            }

            if ($nextMarker === "") {
                break;
            }
        }
    }

    private function listBlobs(?string $prefix, ?string $delimiter, string $marker, ?int $maxResults): ListBlobsResponseBody
    {
        $response = $this->client->request('GET', $this->uri, [
            'query' => [
                'restype' => 'container',
                'comp' => 'list',
                'prefix' => $prefix,
                'marker' => $marker !== "" ? $marker : null,
                'delimiter' => $delimiter,
                'maxresults' => $maxResults,
            ],
        ]);

        return ListBlobsResponseBody::fromXml(new \SimpleXMLElement($response->getBody()->getContents()));
    }
}
