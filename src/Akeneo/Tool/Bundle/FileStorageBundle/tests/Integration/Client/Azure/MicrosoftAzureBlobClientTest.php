<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\FileStorageBundle\tests\Integration\Client\Azure;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\FileStorageBundle\Client\Azure\MicrosoftAzureBlobServiceClient;
use Akeneo\Tool\Bundle\FileStorageBundle\tests\Integration\MockGuzzle;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;

final class MicrosoftAzureBlobClientTest extends TestCase
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

    public function test_it_downloads_streaming(): void
    {
        $blobClient = $this->azureServiceClient->getContainerClient('mycontainer')->getMicrosoftAzureBlobClient('myblob');
        $blobClient->setClient($this->guzzle);
        $uri = new Uri('http://127.0.0.1:10000/devstoreaccount1/mycontainer/myblob');

        $this->mockGuzzleResponse(
            client: $this->guzzle,
            method: 'request',
            arguments: ['GET', $uri, ['stream' => true]],
            contents: fn () => \json_encode([]),
            headers: [
                'Last-Modified' => (new \DateTime())->format(DATE_RFC1123),
                'Content-Length' => '10',
                'Content-Type' => 'contenttype',
                'Content-MD5' => '',
            ],
        );

        $blobClient->downloadStreaming();

        $this->assertGuzzleRequestWasMade('GET', '/devstoreaccount1/mycontainer/myblob', ['stream' => true]);
    }

    public function test_download_streaming_throws_error_if_container_does_not_exist(): void
    {
        $blobClient = $this->azureServiceClient->getContainerClient('othercontainer')->getMicrosoftAzureBlobClient('myblob');
        $blobClient->setClient($this->guzzle);
        $uri = new Uri('http://127.0.0.1:10000/devstoreaccount1/othercontainer/myblob');

        $this->mockGuzzleException(
            client: $this->guzzle,
            method: 'request',
            arguments: ['GET', $uri, ['stream' => true]],
            headers: [
                'Last-Modified' => (new \DateTime())->format(DATE_RFC1123),
                'Content-Length' => '10',
                'Content-Type' => 'contenttype',
                'Content-MD5' => '',
            ],
        );


        $this->expectExceptionMessage('RequestException');

        $blobClient->downloadStreaming();

        $this->assertGuzzleRequestWasMade('GET', '/devstoreaccount1/othercontainer/myblob', ['stream' => true]);
    }

    public function test_get_blob_properties(): void
    {
        $blobClient = $this->azureServiceClient->getContainerClient('mycontainer')->getMicrosoftAzureBlobClient('myblob');
        $blobClient->setClient($this->guzzle);
        $uri = new Uri('http://127.0.0.1:10000/devstoreaccount1/mycontainer/myblob');

        $this->mockGuzzleResponse(
            client: $this->guzzle,
            method: 'request',
            arguments: ['HEAD', $uri, []],
            contents: fn () => \json_encode([]),
            headers: [
                'Last-Modified' => (new \DateTime())->format(DATE_RFC1123),
                'Content-Length' => '10',
                'Content-Type' => 'contenttype',
                'Content-MD5' => '',
            ],
        );

        $blobClient->getProperties();

        $this->assertGuzzleRequestWasMade('GET', '/devstoreaccount1/mycontainer/myblob', []);
    }

    public function test_get_blob_properties_throws_exception(): void
    {
        $blobClient = $this->azureServiceClient->getContainerClient('othercontainer')->getMicrosoftAzureBlobClient('myblob');
        $blobClient->setClient($this->guzzle);
        $uri = new Uri('http://127.0.0.1:10000/devstoreaccount1/othercontainer/myblob');

        $this->mockGuzzleException(
            client: $this->guzzle,
            method: 'request',
            arguments: ['HEAD', $uri, []],
            headers: [
                'Last-Modified' => (new \DateTime())->format(DATE_RFC1123),
                'Content-Length' => '10',
                'Content-Type' => 'contenttype',
                'Content-MD5' => '',
            ],
        );

        $this->expectExceptionMessage('RequestException');

        $blobClient->getProperties();

        $this->assertGuzzleRequestWasMade('GET', '/devstoreaccount1/othercontainer/myblob', []);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
