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
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;

final class MediaFileInstaller implements FixtureInstaller
{
    private FixtureReader $fixtureReader;

    private SaverInterface $saver;

    public function __construct(FixtureReader $fixtureReader, SaverInterface $saver)
    {
        $this->fixtureReader = $fixtureReader;
        $this->saver = $saver;
    }

    public function install(): void
    {
        foreach ($this->fixtureReader->read() as $mediaFileData) {
            $mediaFile = $this->buildMediaFileFromData($mediaFileData);
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
            ->setHash($mediaFileData['hash'])
            ->setStorage(FileStorage::CATALOG_STORAGE_ALIAS)
        ;
        return $mediaFile;
    }
}
