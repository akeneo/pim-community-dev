<?php

declare(strict_types=1);

/*
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace AkeneoTest\Platform\Acceptance\ImportExport;

use Akeneo\Platform\Bundle\ImportExportBundle\Application\DownloadFileFromStorage\DownloadFileFromStorageCommand;
use Akeneo\Platform\Bundle\ImportExportBundle\Application\DownloadFileFromStorage\DownloadFileFromStorageHandler;
use League\Flysystem\Filesystem;

class DownloadFileFromLocalStorageTest extends AcceptanceTestCase
{
    /**
     * @test
     */
    public function it_downloads_file_from_local_storage()
    {
        $this->getLocalFilesystem()->write('a_file_path', 'file content');

        $storage = ['type' => 'local', 'file_path' => '/a_file_path'];

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
}
