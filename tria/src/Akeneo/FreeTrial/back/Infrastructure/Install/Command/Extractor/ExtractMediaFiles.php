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
use Symfony\Component\Console\Output\OutputInterface;

final class ExtractMediaFiles
{
    use InstallCatalogTrait;

    private AkeneoPimClientInterface $apiClient;

    private OutputInterface $output;

    private FilesystemInterface $filesystem;

    public function __construct(FilesystemInterface $filesystem, AkeneoPimClientInterface $apiClient, OutputInterface $output)
    {
        $this->apiClient = $apiClient;
        $this->output = $output;
        $this->filesystem = $filesystem;
    }

    public function __invoke(): void
    {
        $this->output->write('Extract media files... ');

        $mediaFilesApi = $this->apiClient->getProductMediaFileApi();

        file_put_contents($this->getMediaFileFixturesPath(), '');
        $total = 0;

        foreach ($mediaFilesApi->all() as $mediaFile) {
            unset($mediaFile['_links']);

            if ($this->filesystem->has($mediaFile['code'])) {
                $mediaFileContent = $this->filesystem->read($mediaFile['code']);
            } else {
                $mediaFileContent = $this->downloadMediaFile($mediaFile, $mediaFilesApi);
            }

            $mediaFile['hash'] = sha1($mediaFileContent);

            file_put_contents($this->getMediaFileFixturesPath(), json_encode($mediaFile) . PHP_EOL, FILE_APPEND);
            $total++;
        }

        $this->output->writeln(sprintf('%d media files extracted', $total));
    }

    private function downloadMediaFile(array $mediaFileData, MediaFileApiInterface $mediaFileApi): string
    {
        $mediaFileContent = $mediaFileApi->download($mediaFileData['code'])->getBody();

        $options['ContentType'] = $mediaFileData['mime_type'];
        $options['metadata']['contentType'] = $mediaFileData['mime_type'];

        $isFileWritten = $this->filesystem->put($mediaFileData['code'], strval($mediaFileContent), $options);
        if (!$isFileWritten) {
            throw new \Exception('Failed to write media-file ' . $mediaFileData['code']);
        }

        return strval($mediaFileContent);
    }
}
