<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\FileStorage\tests\integration\File;

use Akeneo\Pim\Enrichment\Component\FileStorage;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileStorerIntegration extends TestCase
{
    public function testStoreOneFile()
    {
        $fixturesFilePath = __DIR__ . '/../../fixtures/akeneo.jpg';
        $rawFile = new \SplFileInfo($fixturesFilePath);
        $storer = $this->getFileStorer();
        $file = $storer->store($rawFile, FileStorage::CATALOG_STORAGE_ALIAS, false);

        $this->assertInstanceOf(FileInfoInterface::class, $file);
    }

    public function testStoreTwoIdenticFiles()
    {
        $fixturesFilePath = __DIR__ . '/../../fixtures/akeneo.jpg';
        $rawFile = new \SplFileInfo($fixturesFilePath);
        $storer = $this->getFileStorer();
        $file1 = $storer->store($rawFile, FileStorage::CATALOG_STORAGE_ALIAS, false);
        $file2 = $storer->store($rawFile, FileStorage::CATALOG_STORAGE_ALIAS, false);

        $this->assertInstanceOf(FileInfoInterface::class, $file1);
        $this->assertInstanceOf(FileInfoInterface::class, $file2);
        $this->assertEquals($file1->getKey(), $file2->getKey());
        $this->assertEquals($file1->getHash(), $file2->getHash());
    }

    public function testStoreTwoDifferentFiles()
    {
        $rawFile1 = new \SplFileInfo(__DIR__ . '/../../fixtures/akeneo.jpg');
        $rawFile2 = new \SplFileInfo(__DIR__ . '/../../fixtures/akeneo.png');
        $storer = $this->getFileStorer();
        $file1 = $storer->store($rawFile1, FileStorage::CATALOG_STORAGE_ALIAS, false);
        $file2 = $storer->store($rawFile2, FileStorage::CATALOG_STORAGE_ALIAS, false);

        $this->assertInstanceOf(FileInfoInterface::class, $file1);
        $this->assertInstanceOf(FileInfoInterface::class, $file2);
        $this->assertNotEquals($file1->getKey(), $file2->getKey());
        $this->assertNotEquals($file1->getHash(), $file2->getHash());
    }

    private function getFileStorer(): FileStorerInterface
    {
        return $this->get('akeneo_file_storage.file_storage.file.file_storer');
    }

    protected function getConfiguration()
    {
        return null;
    }
}
