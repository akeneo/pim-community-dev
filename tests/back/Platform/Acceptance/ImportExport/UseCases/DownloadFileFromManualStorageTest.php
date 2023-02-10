<?php

declare(strict_types=1);

/*
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace AkeneoTest\Platform\Acceptance\ImportExport\UseCases;

use Akeneo\Platform\Bundle\ImportExportBundle\Application\DownloadFileFromStorage\DownloadFileFromStorageCommand;
use Akeneo\Platform\Bundle\ImportExportBundle\Application\DownloadFileFromStorage\DownloadFileFromStorageHandler;
use AkeneoTest\Platform\Acceptance\ImportExport\AcceptanceTestCase;
use League\Flysystem\Filesystem;

class DownloadFileFromManualStorageTest extends AcceptanceTestCase
{
    /**
     * @test
     */
    public function it_downloads_file_from_manual_storage()
    {
        $this->getJobFilesystem()->write('a_file_path', 'file content');

        $storage = ['type' => 'manual_upload', 'file_path' => 'a_file_path'];

        $filePath = $this->getHandler()->handle(new DownloadFileFromStorageCommand($storage, '/tmp/job_name/'));
        $this->assertEquals('/tmp/job_name/a_file_path', $filePath);

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
