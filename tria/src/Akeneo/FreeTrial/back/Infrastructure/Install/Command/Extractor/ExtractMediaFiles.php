<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\FreeTrial\Infrastructure\Install\Command\Extractor;

use Akeneo\FreeTrial\Infrastructure\Install\InstallCatalogTrait;
use Akeneo\Pim\ApiClient\AkeneoPimClientInterface;
use Akeneo\Pim\ApiClient\Api\MediaFileApiInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToWriteFile;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;

final class ExtractMediaFiles
{
    use InstallCatalogTrait;

    private const REQUEST_CONCURRENCY = 20;
    private string $mediaDownloadUri;
    private Client $guzzleClient;

    public function __construct(
        private FilesystemOperator $filesystem,
        private AkeneoPimClientInterface $apiClient,
        private string $apibaseUrl,
        private OutputInterface $output
    ) {
        $this->mediaDownloadUri = rtrim($this->apibaseUrl, '/') . '/api/rest/v1/media-files/%s/download';
        $this->guzzleClient = new Client();
    }

    public function __invoke(): void
    {
        $this->output->write('Extract media files... ');

        $mediaFilesApi = $this->apiClient->getProductMediaFileApi();

        file_put_contents($this->getMediaFileFixturesPath(), '');


        $mediaFilesIndexedByCode = array_column(iterator_to_array($mediaFilesApi->all(pageSize: 100)), null, 'code');

        $total = $this->downloadAndSaveConcurrently($mediaFilesIndexedByCode);
        $this->output->writeln(sprintf('%d media files extracted', $total));
    }

    private function downloadAndSaveConcurrently(array $mediaFilesIndexedByCode): int
    {
        $total = 0;
        $uri = $this->mediaDownloadUri;

        $headers = [
            'Accept' => '*/*',
            'Authorization' => sprintf('Bearer %s', $this->apiClient->getToken())
        ];

        $requests = function () use ($mediaFilesIndexedByCode, $headers, $uri) {
            foreach ($mediaFilesIndexedByCode as $mediaFileCode => $mediaFile) {
                yield $mediaFileCode => new Request('GET', sprintf($uri, $mediaFileCode), $headers);
            }
        };

        $pool = new Pool($this->guzzleClient, $requests(), [
            'concurrency' => self::REQUEST_CONCURRENCY,
            'fulfilled' => function (Response $response, $index) use (&$total, $mediaFilesIndexedByCode) {
                $mediaFileCode = $index;
                Assert::keyExists($mediaFilesIndexedByCode, $mediaFileCode);
                $mediaFile = $mediaFilesIndexedByCode[$mediaFileCode];

                $options['ContentType'] = $mediaFile['mime_type'];
                $options['metadata']['contentType'] = $mediaFile['mime_type'];

                $body = strval($response->getBody());
                try {
                    $this->filesystem->write($mediaFileCode, $body, $options);
                } catch (UnableToWriteFile $exception) {
                    throw new \Exception('Failed to write media-file ' . $mediaFileCode, $exception->getCode(), $exception);
                }

                $mediaFile['hash'] = sha1($body);
                unset($mediaFile['_links']);

                file_put_contents($this->getMediaFileFixturesPath(), json_encode($mediaFile) . PHP_EOL, FILE_APPEND);
                $total++;
            },
            'rejected' => function (RequestException $reason, $index) {
                throw new \Exception('Failed to download media-file ' . $index, $reason->getCode(), $reason);
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();

        return $total;
    }
}
