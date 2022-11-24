<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\JobAutomation\Test\Acceptance\UseCases;

use Akeneo\Platform\Bundle\ImportExportBundle\Application\TransferFilesToStorage\FileToTransfer;
use Akeneo\Platform\Bundle\ImportExportBundle\Application\TransferFilesToStorage\TransferFilesToStorageCommand;
use Akeneo\Platform\Bundle\ImportExportBundle\Application\TransferFilesToStorage\TransferFilesToStorageHandler;
use Akeneo\Platform\JobAutomation\Test\Acceptance\AcceptanceTestCase;
use League\Flysystem\Filesystem;

class TransferFilesToSftpStorageTest extends AcceptanceTestCase
{
    /**
     * @test
     */
    public function it_transfers_files_to_sftp_storage(): void
    {
        $this->getLocalFilesystem()->write('file_key1', 'file1 content');
        $this->getCatalogFilesystem()->write('file_key2', 'file2 content');

        $storage = [
            'type' => 'sftp',
            'file_path' => 'a_file_path',
            'host' => 'localhost',
            'port' => 22,
            'login_type' => 'password',
            'username' => 'root',
            'password' => 'root',
        ];

        $filesToTransfer = [
            new FileToTransfer('file_key1', 'localFilesystem', 'filename1.csv', false),
            new FileToTransfer('file_key2', 'catalogStorage', 'filename2.csv', false),
        ];

        $this->getHandler()->handle(new TransferFilesToStorageCommand($filesToTransfer, $storage));

        $this->assertTrue($this->getSftpFilesystem()->fileExists('filename1.csv'));
        $this->assertTrue($this->getSftpFilesystem()->fileExists('filename2.csv'));

        $this->assertEquals('file1 content', $this->getSftpFilesystem()->read('filename1.csv'));
        $this->assertEquals('file2 content', $this->getSftpFilesystem()->read('filename2.csv'));
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

    private function getSftpFilesystem(): Filesystem
    {
        return $this->get('oneup_flysystem.sftp_storage_filesystem');
    }
}
