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

namespace Akeneo\FreeTrial\Infrastructure\Install\Command;

use Akeneo\Pim\ApiClient\AkeneoPimClientBuilder;
use Akeneo\Pim\ApiClient\AkeneoPimClientInterface;
use Akeneo\Pim\ApiClient\Api\MediaFileApiInterface;
use Akeneo\Pim\Enrichment\Component\FileStorage;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\MountManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DownloadMediaFilesCommand extends Command
{
    private FilesystemInterface $fileSystem;

    public function __construct(MountManager $fileSystemManager)
    {
        parent::__construct();

        $this->fileSystem = $fileSystemManager->getFilesystem(FileStorage::CATALOG_STORAGE_ALIAS);
    }

    protected function configure()
    {
        $this
            ->setName('akeneo:free-trial:download-media-files')
            ->setDescription('Download the media-files of the Free-Trial reference catalog using the public API.')
            ->addArgument('api-url', InputArgument::REQUIRED, 'API URL')
            ->addArgument('api-client-id', InputArgument::REQUIRED, 'API client id')
            ->addArgument('api-secret', InputArgument::REQUIRED, 'API secret')
            ->addArgument('api-username', InputArgument::REQUIRED, 'API username')
            ->addArgument('api-password', InputArgument::REQUIRED, 'API password')
            ->setHidden(true);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title(sprintf('Download media-files from %s', $input->getArgument('api-url')));

        $apiClient = $this->buildApiClient($input);
        $mediaFilesApi = $apiClient->getProductMediaFileApi();

        $io->progressStart($this->getTotalMediaFiles($mediaFilesApi));

        foreach ($mediaFilesApi->all() as $mediaFile) {
            $this->downloadMediaFile($this->buildMediaFileFromData($mediaFile), $mediaFilesApi);
            $io->progressAdvance(1);
        }

        $io->progressFinish();
    }

    private function getTotalMediaFiles(MediaFileApiInterface $mediaFileApi): int
    {
        $mediaFiles = $mediaFileApi->listPerPage(1, true);

        return $mediaFiles->getCount();
    }

    private function downloadMediaFile(FileInfoInterface $mediaFile, MediaFileApiInterface $mediaFileApi): void
    {
        $mediaFileContent = $mediaFileApi->download($mediaFile->getKey())->getBody();

        $options['ContentType'] = $mediaFile->getMimeType();
        $options['metadata']['contentType'] = $mediaFile->getMimeType();

        $isFileWritten = $this->fileSystem->put($mediaFile->getKey(), $mediaFileContent, $options);
        if (!$isFileWritten) {
            throw new \Exception('Failed to write media-file ' . $mediaFile->getKey());
        }
    }

    private function buildMediaFileFromData(array $mediaFileData): FileInfoInterface
    {
        $mediaFile = new FileInfo();
        $mediaFile
            ->setKey($mediaFileData['code'])
            ->setOriginalFilename($mediaFileData['original_filename'])
            ->setMimeType($mediaFileData['mime_type'])
            ->setSize($mediaFileData['size'])
            ->setExtension($mediaFileData['extension'])
            ->setStorage(FileStorage::CATALOG_STORAGE_ALIAS)
        ;
        return $mediaFile;
    }

    private function buildApiClient(InputInterface $input): AkeneoPimClientInterface
    {
        $clientBuilder = new AkeneoPimClientBuilder($input->getArgument('api-url'));

        return $clientBuilder->buildAuthenticatedByPassword(
            $input->getArgument('api-client-id'),
            $input->getArgument('api-secret'),
            $input->getArgument('api-username'),
            $input->getArgument('api-password')
        );
    }
}
