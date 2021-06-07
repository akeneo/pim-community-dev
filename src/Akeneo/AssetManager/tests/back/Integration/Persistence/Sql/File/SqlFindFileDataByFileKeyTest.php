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

namespace Akeneo\AssetManager\Integration\Persistence\Sql\File;

use Akeneo\AssetManager\Infrastructure\Filesystem\Storage;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\File\SqlFindFileDataByFileKey;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;

class SqlFindFileDataByFileKeyTest extends SqlIntegrationTestCase
{
    private SqlFindFileDataByFileKey $findfileByFileKey;

    private SaverInterface $fileSaver;

    public function setUp(): void
    {
        parent::setUp();

        $this->findfileByFileKey = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_file_data_by_file_key');
        $this->fileSaver = $this->get('akeneo_file_storage.saver.file');
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();

        $this->loadfile();
    }

    /**
     * @test
     */
    public function it_returns_the_file_data_for_a_given_file_key()
    {
        $fileData = $this->findfileByFileKey->find('files/starck.jpg');

        $this->assertSame([
            'filePath'         => 'files/starck.jpg',
            'originalFilename' => 'starck.jpg',
            'size'             => 1024,
            'mimeType'         => 'file/jpg',
            'extension'        => 'jpg',
        ], $fileData);
    }

    /**
     * @test
     */
    public function it_returns_null_if_no_file_was_found()
    {
        $file = $this->findfileByFileKey->find('files/smith.jpg');

        $this->assertNull($file);
    }

    private function loadfile()
    {
        $file = new FileInfo();
        $file->setKey('files/starck.jpg');
        $file->setMimeType('file/jpg');
        $file->setOriginalFilename('starck.jpg');
        $file->setSize(1024);
        $file->setExtension('jpg');
        $file->setHash(sha1('Starck image'));
        $file->setStorage(Storage::FILE_STORAGE_ALIAS);

        $this->fileSaver->save($file);
    }
}
