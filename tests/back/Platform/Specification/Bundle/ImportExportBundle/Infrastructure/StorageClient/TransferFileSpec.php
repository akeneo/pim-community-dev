<?php

declare(strict_types=1);

/*
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Specification\Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\StorageClientInterface;
use PhpSpec\ObjectBehavior;

class TransferFileSpec extends ObjectBehavior
{
    public function it_transfers_file_from_a_source_fs_to_a_destination_fs(
        StorageClientInterface $sourceFilesystem,
        StorageClientInterface $destinationFilesystem,
    ): void {
        $sourceFilePath = 'my_export.xlsx';
        $destinationFilePath = 'my_export_on_server.xlsx';

        $sourceFilesystem->fileExists($sourceFilePath)->shouldBeCalled()->willReturn(true);

        $sourceStream = fopen('php://memory', 'r');
        $sourceFilesystem->readStream($sourceFilePath)->shouldBeCalled()->willReturn($sourceStream);

        $expectedTmpDestinationFilePath = '.tmp-my_export_on_server.xlsx';
        $destinationFilesystem->writeStream($expectedTmpDestinationFilePath, $sourceStream)->shouldBeCalled();
        $destinationFilesystem->fileExists($destinationFilePath)->shouldBeCalled()->willReturn(false);
        $destinationFilesystem->move($expectedTmpDestinationFilePath, $destinationFilePath)->shouldBeCalled();

        $this->transfer($sourceFilesystem, $destinationFilesystem, $sourceFilePath, $destinationFilePath);
    }

    public function it_transfers_file_with_a_dirname_from_a_source_fs_to_a_destination_fs(
        StorageClientInterface $sourceFilesystem,
        StorageClientInterface $destinationFilesystem,
    ): void {
        $sourceFilePath = 'my_export.xlsx';
        $destinationFilePath = 'exports/test/my_export_on_server.xlsx';

        $sourceFilesystem->fileExists($sourceFilePath)->shouldBeCalled()->willReturn(true);

        $sourceStream = fopen('php://memory', 'r');
        $sourceFilesystem->readStream($sourceFilePath)->shouldBeCalled()->willReturn($sourceStream);

        $expectedTmpDestinationFilePath = 'exports/test/.tmp-my_export_on_server.xlsx';
        $destinationFilesystem->writeStream($expectedTmpDestinationFilePath, $sourceStream)->shouldBeCalled();
        $destinationFilesystem->fileExists($destinationFilePath)->shouldBeCalled()->willReturn(true);
        $destinationFilesystem->delete($destinationFilePath)->shouldBeCalled();
        $destinationFilesystem->move($expectedTmpDestinationFilePath, $destinationFilePath)->shouldBeCalled();

        $this->transfer($sourceFilesystem, $destinationFilesystem, $sourceFilePath, $destinationFilePath);
    }

    public function it_throws_exception_when_source_file_does_not_exist(
        StorageClientInterface $sourceFilesystem,
        StorageClientInterface $destinationFilesystem,
    ): void {
        $sourceFilePath = 'my_export.xlsx';

        $sourceFilesystem->fileExists($sourceFilePath)->willReturn(false);
        $this->shouldThrow(\RuntimeException::class)->during('transfer', [$sourceFilesystem, $destinationFilesystem, $sourceFilePath, 'my_export_on_server.xlsx']);
    }

    public function it_throws_exception_when_unable_to_read_from_storage(
        StorageClientInterface $sourceFilesystem,
        StorageClientInterface $destinationFilesystem,
    ): void {
        $sourceFilePath = 'my_export.xlsx';

        $sourceFilesystem->fileExists($sourceFilePath)->shouldBeCalled()->willReturn(true);

        $sourceFilesystem->readStream($sourceFilePath)->shouldBeCalled()->willThrow(\Exception::class);

        $this->shouldThrow(\RuntimeException::class)->during('transfer', [$sourceFilesystem, $destinationFilesystem, $sourceFilePath, 'my_export_on_server.xlsx']);
    }
}
