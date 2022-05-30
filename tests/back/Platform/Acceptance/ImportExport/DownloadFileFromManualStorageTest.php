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

namespace AkeneoTest\Platform\Acceptance\ImportExport;

use Akeneo\Platform\Bundle\ImportExportBundle\Application\DownloadFileFromStorage\DownloadFileFromStorageCommand;
use Akeneo\Platform\Bundle\ImportExportBundle\Application\DownloadFileFromStorage\DownloadFileFromStorageHandler;
use League\Flysystem\Filesystem;

class DownloadFileFromManualStorageTest extends AcceptanceTestCase
{
    /**
     * @test
     */
    public function it_downloads_file_from_manual_storage()
    {
        $this->getJobFilesystem()->write('a_file_path', 'file content');

        $storage = ['type' => 'manual', 'file_path' => 'a_file_path'];

        $this->getHandler()->handle(new DownloadFileFromStorageCommand($storage, '/tmp/job_name/'));

        $this->assertTrue($this->getLocalFilesystem()->fileExists('/tmp/job_name/a_file_path'));
        $this->assertEquals('file content', $this->getLocalFilesystem()->read('/tmp/job_name/a_file_path'));
    }

    private function getHandler(): DownloadFileFromStorageHandler
    {
        return $this->get('Akeneo\Platform\Bundle\ImportExportBundle\Application\DownloadFileFromStorage\DownloadFileFromStorageHandler');
    }

    private function getLocalFilesystem(): Filesystem
    {
        return $this->get('oneup_flysystem.local_storage_filesystem');
    }

    private function getJobFilesystem(): Filesystem
    {
        return $this->get('oneup_flysystem.jobs_storage_filesystem');
    }
}
