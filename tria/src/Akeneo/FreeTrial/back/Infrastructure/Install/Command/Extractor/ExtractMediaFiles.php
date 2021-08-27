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
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Console\Style\StyleInterface;

final class ExtractMediaFiles
{
    use InstallCatalogTrait;

    private AkeneoPimClientInterface $apiClient;

    private StyleInterface $io;

    private FilesystemInterface $filesystem;

    public function __construct(FilesystemInterface $filesystem, AkeneoPimClientInterface $apiClient, StyleInterface $io)
    {
        $this->apiClient = $apiClient;
        $this->io = $io;
        $this->filesystem = $filesystem;
    }

    public function __invoke(): void
    {
        $this->io->section('Extract media files');

        $mediaFilesApi = $this->apiClient->getProductMediaFileApi();
        $this->io->progressStart($mediaFilesApi->listPerPage(1, true)->getCount());

        file_put_contents($this->getMediaFileFixturesPath(), '');

        foreach ($mediaFilesApi->all() as $mediaFile) {
            unset($mediaFile['_links']);

            if ($this->filesystem->has($mediaFile['code'])) {
                $mediaFileContent = $this->filesystem->read($mediaFile['code']);
            } else {
                $mediaFileContent = $this->downloadMediaFile($mediaFile, $mediaFilesApi);
            }

            $mediaFile['hash'] = sha1($mediaFileContent);

            file_put_contents($this->getMediaFileFixturesPath(), json_encode($mediaFile) . PHP_EOL, FILE_APPEND);
            $this->io->progressAdvance(1);
        }

        $this->io->progressFinish();
    }

    private function downloadMediaFile(array $mediaFileData, MediaFileApiInterface $mediaFileApi): string
    {
        $mediaFileContent = $mediaFileApi->download($mediaFileData['code'])->getBody();

        $options['ContentType'] = $mediaFileData['mime_type'];
        $options['metadata']['contentType'] = $mediaFileData['mime_type'];

        $isFileWritten = $this->filesystem->put($mediaFileData['code'], $mediaFileContent, $options);
        if (!$isFileWritten) {
            throw new \Exception('Failed to write media-file ' . $mediaFileData['code']);
        }

        return strval($mediaFileContent);
    }
}
