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

namespace Akeneo\Platform\TailoredImport\Test\Acceptance\UseCases\ReadColumns;

use Akeneo\Platform\TailoredImport\Application\ReadColumns\ReadColumnsHandler;
use Akeneo\Platform\TailoredImport\Application\ReadColumns\ReadColumnsQuery;
use Akeneo\Platform\TailoredImport\Domain\Model\Column;
use Akeneo\Platform\TailoredImport\Domain\Model\ColumnCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\File\FileStructure;
use Akeneo\Platform\TailoredImport\Domain\Model\Filesystem\Storage;
use Akeneo\Platform\TailoredImport\Test\Acceptance\AcceptanceTestCase;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;

class ReadColumnTest extends AcceptanceTestCase
{
    /**
     * @test
     */
    public function it_return_generated_column_from_the_file(): void
    {
        $fileKey = $this->storeFile();
        $query = new ReadColumnsQuery($fileKey, FileStructure::create(0, 1, 2, 0,'Products'));

        $response = $this->getHandler()->handle($query);
        $expectedResponse = ColumnCollection::create([
            Column::create('b64d1498-b668-4880-81c2-58f7c88375b1', 0, 'Sku'),
            Column::create('b64d1498-b668-4880-81c2-58f7c88375b1', 1, 'Name'),
            Column::create('b64d1498-b668-4880-81c2-58f7c88375b1', 2, 'Price'),
            Column::create('b64d1498-b668-4880-81c2-58f7c88375b1', 3, 'Enabled'),
            Column::create('b64d1498-b668-4880-81c2-58f7c88375b1', 4, 'Release date'),
            Column::create('b64d1498-b668-4880-81c2-58f7c88375b1', 5, 'Price with tax'),
        ]);

        $this->assertEquals($expectedResponse, $response);
    }

    /**
     * @test
     */
    public function it_return_generated_column_from_the_file_that_does_not_start_at_first_column_and_first_line(): void
    {
        $fileKey = $this->storeFile();
        $query = new ReadColumnsQuery($fileKey, FileStructure::create(1, 2, 4, 0,'Empty lines and columns'));

        $response = $this->getHandler()->handle($query);
        $expectedResponse = ColumnCollection::create([
            Column::create('b64d1498-b668-4880-81c2-58f7c88375b1', 1, 'Sku'),
            Column::create('b64d1498-b668-4880-81c2-58f7c88375b1', 2, 'Name'),
            Column::create('b64d1498-b668-4880-81c2-58f7c88375b1', 3, 'Price'),
            Column::create('b64d1498-b668-4880-81c2-58f7c88375b1', 4, 'Enabled'),
            Column::create('b64d1498-b668-4880-81c2-58f7c88375b1', 5, 'Release date'),
            Column::create('b64d1498-b668-4880-81c2-58f7c88375b1', 6, 'Price with tax'),
        ]);

        $this->assertEquals($expectedResponse, $response);
    }

    private function storeFile(): string
    {
        $fileKey = uniqid();

        /** @var FilesystemProvider $fileSystemProvider */
        $fileSystemProvider = $this->get('akeneo_file_storage.file_storage.filesystem_provider');
        $fileSystem = $fileSystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);
        $fileSystem->write($fileKey, file_get_contents(__DIR__ . '/../../../Common/simple_import.xlsx'));

        return $fileKey;
    }

    private function getHandler(): ReadColumnsHandler
    {
        return self::getContainer()->get('akeneo.tailored_import.handler.read_columns');
    }
}
