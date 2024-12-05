<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\FileStorageBundle\Client\Azure;

use Akeneo\Tool\Bundle\FileStorageBundle\Auth\StorageSharedKeyCredential;
use Akeneo\Tool\Bundle\FileStorageBundle\Domain\Model\Azure\Blob\BlobDownloadStreamingResult;
use Akeneo\Tool\Bundle\FileStorageBundle\Domain\Model\Azure\Blob\BlobProperties;
use Akeneo\Tool\Bundle\FileStorageBundle\Domain\Model\Azure\Blob\Requests\Block;
use Akeneo\Tool\Bundle\FileStorageBundle\Domain\Model\Azure\Blob\Requests\BlockType;
use Akeneo\Tool\Bundle\FileStorageBundle\Domain\Model\Azure\Blob\Requests\PutBlockRequestBody;
use Akeneo\Tool\Bundle\FileStorageBundle\Domain\Model\Azure\Blob\UploadBlobOptions;
use Akeneo\Tool\Bundle\FileStorageBundle\Middleware\ClientFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

final class MicrosoftAzureBlobClient
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

    public function downloadStreaming(): BlobDownloadStreamingResult
    {
        $response = $this->client->request('GET', $this->uri, [
            'stream' => true,
        ]);

        return new BlobDownloadStreamingResult(
            $response->getBody(),
            BlobProperties::fromResponseHeaders($response),
        );
    }

    public function getProperties(): BlobProperties
    {
        $response = $this->client->request('HEAD', $this->uri);

        return BlobProperties::fromResponseHeaders($response);
    }

    public function exists(): bool
    {
        try {
            $this->client->request('HEAD', $this->uri);
        } catch(\Exception) {
            return false;
        }
        return true;
    }

    public function delete(): void
    {
        $this->client->delete($this->uri);
    }

    public function deleteIfExists(): void
    {
        try {
            $this->delete();
        } catch (\Exception) {
            // do nothing
        }
    }

    public function upload($content, ?UploadBlobOptions $options = null): void
    {
        $content = Utils::streamFor($content);
        $contentLength = $content->getSize();

        if ($contentLength === null) {
            throw new \Exception('Unable to upload Blob');
        }

        if ($contentLength <= $options->initialTransferSize) {
            $this->uploadSingle($content, $options);
        } else {
            $this->uploadInBlocks($content, $options);
        }
    }

    public function copyFromUri(UriInterface $source): void
    {
        $this->client->request('PUT', $this->uri, [
            'headers' => [
                'x-ms-copy-source' => (string) $source,
            ],
        ]);
    }

    private function uploadSingle(StreamInterface $content, ?UploadBlobOptions $options = null): void
    {
        $this->client->request('PUT', $this->uri, [
            'headers' => [
                'x-ms-blob-type' => 'BlockBlob',
                'Content-Type' => $options->contentType,
                'Content-Length' => $content->getSize(),
            ],
            'body' => $content,
        ]);
    }

    private function uploadInBlocks(StreamInterface $content, ?UploadBlobOptions $options = null): void
    {
        $blocks = [];

        $putBlockRequestGenerator = function () use ($content, $options, &$blocks): \Generator {
            while (true) {
                $blockContent = Utils::streamFor();
                Utils::copyToStream($content, $blockContent, $options->maximumTransferSize);

                if($blockContent->getSize() === 0) {
                    break;
                }

                $blockId = str_pad((string) count($blocks), 6, '0', STR_PAD_LEFT);
                $block = new Block($blockId, BlockType::UNCOMMITTED);
                $blocks[] = $block;

                yield fn () => $this->putBlockAsync($block, $blockContent);
            }
        };

        $pool = new Pool($this->client, $putBlockRequestGenerator(), [
            'concurrency' => $options->maximumConcurrency,
            'rejected' => function (\Exception $e) {
                throw $e;
            },
        ]);

        $pool->promise()->wait();

        $this->putBlockList(
            $blocks,
            $options->contentType,
            Utils::hash($content, 'md5', true),
        );
    }

    private function putBlockAsync(Block $block, StreamInterface $content): PromiseInterface
    {
        return $this->client
            ->putAsync($this->uri, [
                'query' => [
                    'comp' => 'block',
                    'blockid' => base64_encode($block->id),
                ],
                'headers' => [
                    'Content-Length' => $content->getSize(),
                ],
                'body' => $content,
            ]);
    }

    /**
     * @param Block[] $blocks
     */
    private function putBlockList(array $blocks, ?string $contentType, string $contentMD5): void
    {
        $this->client->request('PUT', $this->uri, [
            'query' => [
                'comp' => 'blocklist',
            ],
            'headers' => [
                'x-ms-blob-content-type' => $contentType,
                'x-ms-blob-content-md5' => base64_encode($contentMD5),
            ],
            'body' => (new PutBlockRequestBody($blocks))->toXml()->asXML(),
        ]);
    }
}
