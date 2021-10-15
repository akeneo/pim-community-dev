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

namespace Akeneo\FreeTrial\Infrastructure\Install\Installer;

use Akeneo\FreeTrial\Infrastructure\Install\Reader\FixtureReader;
use Akeneo\Pim\Enrichment\Component\FileStorage;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;

final class MediaFileInstaller implements FixtureInstaller
{
    private FixtureReader $fixtureReader;

    private BulkSaverInterface $saver;

    private int $batchSize;

    private const DEFAULT_BATCH_SIZE = 100;

    public function __construct(
        FixtureReader $fixtureReader,
        BulkSaverInterface $saver,
        int $batchSize = self::DEFAULT_BATCH_SIZE
    ) {
        $this->fixtureReader = $fixtureReader;
        $this->saver = $saver;
        $this->batchSize = $batchSize;
    }

    public function install(): void
    {
        $mediaFiles = [];
        foreach ($this->fixtureReader->read() as $mediaFileData) {
            $mediaFiles[] = $this->buildMediaFileFromData($mediaFileData);

            if (count($mediaFiles) % $this->batchSize === 0) {
                $this->saver->saveAll($mediaFiles);
                $mediaFiles = [];
            }
        }

        if (!empty($mediaFiles)) {
            $this->saver->saveAll($mediaFiles);
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
            ->setHash($mediaFileData['hash'])
            ->setStorage(FileStorage::CATALOG_STORAGE_ALIAS)
        ;
        return $mediaFile;
    }
}
