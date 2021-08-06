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

namespace Akeneo\FreeTrial\Infrastructure\Install\EventSubscriber;

use Akeneo\FreeTrial\Infrastructure\Install\InstallCatalogTrait;
use Akeneo\Pim\Enrichment\Component\FileStorage;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use League\Flysystem\MountManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class InstallMediaFilesSubscriber implements EventSubscriberInterface
{
    use InstallCatalogTrait;

    private MountManager $fileSystemManager;

    private SaverInterface $saver;

    public function __construct(
        MountManager $mountManager,
        SaverInterface $saver
    ) {
        $this->fileSystemManager = $mountManager;
        $this->saver = $saver;
    }

    public static function getSubscribedEvents()
    {
        return [
            InstallerEvents::PRE_LOAD_FIXTURES => 'installMediaFiles',
        ];
    }

    public function installMediaFiles(InstallerEvent $installerEvent): void
    {
        if (!$this->isFreeTrialCatalogInstallation($installerEvent)) {
            return;
        }

        $filesystem = $this->fileSystemManager->getFilesystem(FileStorage::CATALOG_STORAGE_ALIAS);
        $mediaFiles = fopen($this->getMediaFilesFixturesPath(), 'r');

        while ($mediaFileData = fgets($mediaFiles)) {
            $mediaFileData = json_decode($mediaFileData, true);
            $mediaFile = $this->buildMediaFileFromData($mediaFileData);

            $fileResource = fopen($this->getMediaFilesFixturesDirectoryPath() . '/' . $mediaFile->getKey(), 'r');
            if (false === $fileResource) {
                throw new \Exception('Failed to open media-file ' . $mediaFile->getKey());
            }

            $options['ContentType'] = $mediaFile->getMimeType();
            $options['metadata']['contentType'] = $mediaFile->getMimeType();

            $isFileWritten = $filesystem->writeStream($mediaFile->getKey(), $fileResource, $options);
            if (!$isFileWritten) {
                throw new \Exception('Failed to write media-file ' . $mediaFile->getKey());
            }

            $this->saver->save($mediaFile);
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
            ->setHash(sha1_file($this->getMediaFilesFixturesDirectoryPath() . '/' . $mediaFile->getKey()))
            ->setStorage(FileStorage::CATALOG_STORAGE_ALIAS)
        ;
        return $mediaFile;
    }
}
