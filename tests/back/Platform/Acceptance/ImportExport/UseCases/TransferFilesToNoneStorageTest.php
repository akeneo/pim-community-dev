<?php

declare(strict_types=1);

/*
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace AkeneoTest\Platform\Acceptance\ImportExport\UseCases;

use Akeneo\Platform\Bundle\ImportExportBundle\Application\TransferFilesToStorage\FileToTransfer;
use Akeneo\Platform\Bundle\ImportExportBundle\Application\TransferFilesToStorage\TransferFilesToStorageCommand;
use Akeneo\Platform\Bundle\ImportExportBundle\Application\TransferFilesToStorage\TransferFilesToStorageHandler;
use AkeneoTest\Platform\Acceptance\ImportExport\AcceptanceTestCase;

class TransferFilesToNoneStorageTest extends AcceptanceTestCase
{
    /**
     * @test
     */
    public function it_does_nothing_when_storage_is_none()
    {
        $storage = ['type' => 'none'];
        $filesToTransfer = [
            new FileToTransfer('file_key1', 'localFilesystem', 'filename1.csv', false),
            new FileToTransfer('file_key2', 'catalogStorage', 'filename2.csv', false),
        ];

        $this->getHandler()->handle(new TransferFilesToStorageCommand($filesToTransfer, $storage));
    }

    private function getHandler(): TransferFilesToStorageHandler
    {
        return $this->get('Akeneo\Platform\Bundle\ImportExportBundle\Application\TransferFilesToStorage\TransferFilesToStorageHandler');
    }
}
