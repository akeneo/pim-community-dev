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

use Akeneo\Platform\Bundle\ImportExportBundle\Application\DownloadFileFromStorage\DownloadFileFromStorageCommand;
use Akeneo\Platform\Bundle\ImportExportBundle\Application\DownloadFileFromStorage\DownloadFileFromStorageHandler;
use Akeneo\Platform\JobAutomation\Test\Acceptance\AcceptanceTestCase;
use League\Flysystem\Filesystem;

class DownloadFileFromSftpStorageTest extends AcceptanceTestCase
{
    /**
     * @test
     */
    public function it_downloads_file_from_sftp_storage(): void
    {
        $this->getSftpFilesystem()->write('a_file_path', 'file content');

        $storage = [
            'type' => 'sftp',
            'file_path' => 'a_file_path',
            'host' => 'localhost',
            'port' => 22,
            'login_type' => 'credentials',
            'username' => 'root',
            'password' => 'root',
        ];

        $this->getHandler()->handle(new DownloadFileFromStorageCommand($storage, '/tmp/job_name/'));

        $this->assertTrue($this->getLocalFilesystem()->fileExists('/tmp/job_name/a_file_path'));
        $this->assertEquals('file content', $this->getLocalFilesystem()->read('/tmp/job_name/a_file_path'));
    }

    private function getHandler(): DownloadFileFromStorageHandler
    {
        return $this->get('Akeneo\Platform\Bundle\ImportExportBundle\Application\DownloadFileFromStorage\DownloadFileFromStorageHandler');
    }

    private function getSftpFilesystem(): Filesystem
    {
        return $this->get('oneup_flysystem.sftp_storage_filesystem');
    }

    private function getLocalFilesystem(): Filesystem
    {
        return $this->get('oneup_flysystem.local_storage_filesystem');
    }
}
