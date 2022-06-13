<?php

declare(strict_types=1);

/*
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace AkeneoTest\Platform\Acceptance\ImportExport;

use Akeneo\Platform\Bundle\ImportExportBundle\Application\TransferFilesToStorage\FileToTransfer;
use Akeneo\Platform\Bundle\ImportExportBundle\Application\TransferFilesToStorage\TransferFilesToStorageCommand;
use Akeneo\Platform\Bundle\ImportExportBundle\Application\TransferFilesToStorage\TransferFilesToStorageHandler;
use League\Flysystem\Filesystem;

class TransferFilesToLocalStorageTest extends AcceptanceTestCase
{
    /**
     * @test
     */
    public function it_transfers_files_to_local_storage()
    {
        $this->getLocalFilesystem()->write('file_key1', 'file1 content');
        $this->getCatalogFilesystem()->write('file_key2', 'file2 content');

        $storage = ['type' => 'local', 'file_path' => '/tmp'];
        $filesToTransfer = [
            new FileToTransfer('file_key1', 'localFilesystem', 'filename1.csv'),
            new FileToTransfer('file_key2', 'catalogStorage', 'filename2.csv'),
        ];

        $this->getHandler()->handle(new TransferFilesToStorageCommand($filesToTransfer, $storage));

        $this->assertTrue($this->getLocalFilesystem()->fileExists('filename1.csv'));
        $this->assertTrue($this->getLocalFilesystem()->fileExists('filename2.csv'));

        $this->assertEquals('file1 content', $this->getLocalFilesystem()->read('filename1.csv'));
        $this->assertEquals('file2 content', $this->getLocalFilesystem()->read('filename2.csv'));
    }

    private function getHandler(): TransferFilesToStorageHandler
    {
        return $this->get('Akeneo\Platform\Bundle\ImportExportBundle\Application\TransferFilesToStorage\TransferFilesToStorageHandler');
    }

    private function getCatalogFilesystem(): Filesystem
    {
        return $this->get('oneup_flysystem.catalog_storage_filesystem');
    }

    private function getLocalFilesystem(): Filesystem
    {
        return $this->get('oneup_flysystem.local_storage_filesystem');
    }
}
