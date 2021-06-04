<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Integration\Persistence\Sql\MediaFile;

use Akeneo\AssetManager\Domain\Repository\MediaFileNotFoundException;
use Akeneo\AssetManager\Domain\Repository\MediaFileRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Filesystem\Storage;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlMediaFileRepositoryTest extends SqlIntegrationTestCase
{
    private MediaFileRepositoryInterface $mediaFileRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->mediaFileRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.media_file');
        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_returns_a_media_file_by_its_identifier()
    {
        $expectedFile = $this->loadMediaFile();

        $mediaFile = $this->mediaFileRepository->getByIdentifier('tests/images/kartell.jpg');

        $this->assertEquals($expectedFile, $mediaFile);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_the_media_file_was_not_found()
    {
        $this->loadMediaFile();

        $this->expectException(MediaFileNotFoundException::class);
        $this->mediaFileRepository->getByIdentifier('unknown_image.png');
    }

    private function loadMediaFile(): FileInfo
    {
        $fileSaver = $this->get('akeneo_file_storage.saver.file');

        $mediaFile = new FileInfo();
        $mediaFile->setKey('tests/images/kartell.jpg');
        $mediaFile->setMimeType('image/jpeg');
        $mediaFile->setOriginalFilename('kartell.jpg');
        $mediaFile->setSize(1024);
        $mediaFile->setExtension('jpg');
        $mediaFile->setHash('imagehash');
        $mediaFile->setStorage(Storage::FILE_STORAGE_ALIAS);

        $fileSaver->save($mediaFile);

        return $mediaFile;
    }

    private function resetDB()
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }
}
