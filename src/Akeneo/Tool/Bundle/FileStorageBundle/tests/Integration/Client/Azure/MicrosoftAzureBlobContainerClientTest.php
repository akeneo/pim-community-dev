<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\FileStorageBundle\tests\Integration\Client\Azure;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\FileStorageBundle\Client\Azure\MicrosoftAzureBlobServiceClient;
use Akeneo\Tool\Bundle\FileStorageBundle\tests\Integration\MockGuzzle;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;

final class MicrosoftAzureBlobContainerClientTest extends TestCase
{
    use MockGuzzle;

    private MicrosoftAzureBlobServiceClient $azureServiceClient;
    private Client $guzzle;

    const CONNECTION_STRING = 'UseDevelopmentStorage=true';

    protected function setUp(): void
    {
        parent::setUp();

        $this->guzzle = $this->createMock(Client::class);

        $this->azureServiceClient = MicrosoftAzureBlobServiceClient::fromConnectionString(self::CONNECTION_STRING);
    }

    public function test_get_container_properties(): void
    {
        $blobContainerClient = $this->azureServiceClient->getContainerClient('mycontainer');
        $blobContainerClient->setClient($this->guzzle);
        $uri = new Uri('http://127.0.0.1:10000/devstoreaccount1/mycontainer');

        $this->mockGuzzleResponse(
            client: $this->guzzle,
            method: 'request',
            arguments: ['GET', $uri, [
                'query' => [
                    'restype' => 'container',
                ],
            ]],
            contents: fn () => \json_encode([]),
            headers: [
                'Last-Modified' => (new \DateTime())->format(\DateTimeInterface::RFC1123),
            ],
        );

        $blobContainerClient->getProperties();

        $this->assertGuzzleRequestWasMade('GET', '/devstoreaccount1/mycontainer', [
            'query' => [
                'restype' => 'container',
            ],
        ]);
    }

    public function test_get_container_properties_throws_exception()
    {
        $blobContainerClient = $this->azureServiceClient->getContainerClient('othercontainer');
        $blobContainerClient->setClient($this->guzzle);
        $uri = new Uri('http://127.0.0.1:10000/devstoreaccount1/othercontainer');

        $this->mockGuzzleException(
            client: $this->guzzle,
            method: 'request',
            arguments: ['GET', $uri, [
                'query' => [
                    'restype' => 'container',
                ],
            ]],
            headers: [
                'Last-Modified' => (new \DateTime())->format(\DateTimeInterface::RFC1123),
            ],
        );

        $this->expectExceptionMessage('RequestException');

        $blobContainerClient->getProperties();

        $this->assertGuzzleRequestWasMade('GET', '/devstoreaccount1/othercontainer', [
            'query' => [
                'restype' => 'container',
            ],
        ]);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
