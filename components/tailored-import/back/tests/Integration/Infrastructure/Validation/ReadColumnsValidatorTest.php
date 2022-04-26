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

namespace Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation;

use Akeneo\Platform\TailoredImport\Domain\Model\Filesystem\Storage;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\ReadColumns;
use Akeneo\Test\Integration\Configuration;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class ReadColumnsValidatorTest extends AbstractValidationTest
{
    public function test_it_does_not_build_violations_when_everything_is_valid(): void
    {
        $fileKey = $this->uploadFile();
        $request = new Request([
            'file_key' => $fileKey,
            'file_structure' => [
                'header_row' => 1,
                'first_column' => 0,
                'first_product_row' => 2,
                'unique_identifier_column' => 1,
                'sheet_name' => 'Products',
            ]
        ]);

        $violations = $this->getValidator()->validate($request, new ReadColumns());

        $this->assertNoViolation($violations);
    }

    public function test_it_builds_violations_when_a_header_is_empty(): void
    {
        $fileKey = $this->uploadFile();
        $request = new Request([
            'file_key' => $fileKey,
            'file_structure' => [
                'header_row' => 1,
                'first_column' => 0,
                'first_product_row' => 2,
                'unique_identifier_column' => 1,
                'sheet_name' => 'Empty header',
            ]
        ]);

        $violations = $this->getValidator()->validate($request, new ReadColumns());

        $this->assertHasValidationError('akeneo.tailored_import.validation.file_structure.header_row_should_not_contain_empty_cell', '', $violations);
    }

    public function test_it_builds_violations_when_a_file_has_more_than_500_columns(): void
    {
        $fileKey = $this->uploadFile();
        $request = new Request([
            'file_key' => $fileKey,
            'file_structure' => [
                'header_row' => 1,
                'first_column' => 0,
                'first_product_row' => 2,
                'unique_identifier_column' => 1,
                'sheet_name' => 'More than 500 cols',
            ]
        ]);

        $violations = $this->getValidator()->validate($request, new ReadColumns());

        $this->assertHasValidationError('akeneo.tailored_import.validation.columns.max_count_reached', '', $violations);
    }

    private function uploadFile(): string
    {
        $uploadedFile = new UploadedFile(__DIR__ . '/../../../Common/simple_import.xlsx', 'filename.xlsx');
        $file = $this->get('akeneo_file_storage.file_storage.file.file_storer')->store($uploadedFile, Storage::FILE_STORAGE_ALIAS);

        return $file->getKey();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
