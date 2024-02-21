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

class DownloadFileFromLocalStorageTest extends AcceptanceTestCase
{
    /**
     * @test
     */
    public function it_does_nothing_when_storage_is_local_storage()
    {
        $this->get('feature_flags')->enable('import_export_local_storage');
        $this->getLocalFilesystem()->write('a_file_path', 'file content');

        $storage = ['type' => 'local', 'file_path' => '/a_file_path'];

        $filePath = $this->getHandler()->handle(new DownloadFileFromStorageCommand($storage, '/tmp/job_name/'));
        $this->assertEquals('/a_file_path', $filePath);
    }

    /**
     * @test
     */
    public function it_throw_an_error_when_local_storage_is_not_available()
    {
        $this->expectExceptionMessage('Local storage cannot be used');
        $this->getLocalFilesystem()->write('a_file_path', 'file content');

        $storage = ['type' => 'local', 'file_path' => '/a_file_path'];

        $this->getHandler()->handle(new DownloadFileFromStorageCommand($storage, '/tmp/job_name/'));
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
