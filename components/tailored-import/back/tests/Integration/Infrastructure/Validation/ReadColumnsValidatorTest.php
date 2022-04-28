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
    /**
     * @dataProvider validRequest
     */
    public function test_it_does_not_build_violations_when_request_is_valid(Request $request): void
    {
        $violations = $this->getValidator()->validate($request, new ReadColumns());

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidRequest
     */
    public function test_it_build_violations_when_request_is_invalid(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        Request $request
    ): void {
        $violations = $this->getValidator()->validate($request, new ReadColumns());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }


    public function validRequest(): array
    {
        $fileKey = $this->uploadFile();

        return [
            'A sheet with header in first row and first column' => [
                new Request([], [
                    'file_key' => $fileKey,
                    'file_structure' => [
                        'header_row' => 1,
                        'first_column' => 0,
                        'first_product_row' => 2,
                        'unique_identifier_column' => 1,
                        'sheet_name' => 'Products',
                    ]
                ]),
            ],
            'A sheet with header in second row and second column' => [
                new Request([], [
                    'file_key' => $fileKey,
                    'file_structure' => [
                        'header_row' => 2,
                        'first_column' => 1,
                        'first_product_row' => 4,
                        'unique_identifier_column' => 1,
                        'sheet_name' => 'Empty lines and columns',
                    ]
                ]),
            ],
            'A sheet with trailing header cell' => [
                new Request([], [
                    'file_key' => $fileKey,
                    'file_structure' => [
                        'header_row' => 2,
                        'first_column' => 1,
                        'first_product_row' => 4,
                        'unique_identifier_column' => 1,
                        'sheet_name' => 'Trailing empty header',
                    ]
                ]),
            ],
        ];
    }

    public function invalidRequest(): array
    {
        $fileKey = $this->uploadFile();

        return [
            'A sheet with empty header cell' => [
                'akeneo.tailored_import.validation.file_structure.header_row_should_not_contain_empty_cell',
                '',
                new Request([], [
                    'file_key' => $fileKey,
                    'file_structure' => [
                        'header_row' => 1,
                        'first_column' => 0,
                        'first_product_row' => 2,
                        'unique_identifier_column' => 1,
                        'sheet_name' => 'Empty header',
                    ]
                ]),
            ],
            'A sheet with more than 500 column' => [
                'akeneo.tailored_import.validation.columns.max_count_reached',
                '',
                new Request([], [
                    'file_key' => $fileKey,
                    'file_structure' => [
                        'header_row' => 1,
                        'first_column' => 0,
                        'first_product_row' => 2,
                        'unique_identifier_column' => 1,
                        'sheet_name' => 'More than 500 cols',
                    ]
                ]),
            ],
            'A sheet with empty header' => [
                'akeneo.tailored_import.validation.file_structure.header_row_should_not_contain_empty_cell',
                '',
                new Request([], [
                    'file_key' => $fileKey,
                    'file_structure' => [
                        'header_row' => 1,
                        'first_column' => 0,
                        'first_product_row' => 2,
                        'unique_identifier_column' => 1,
                        'sheet_name' => 'Empty header',
                    ]
                ]),
            ],
            'A sheet without column' => [
                'akeneo.tailored_import.validation.columns.at_least_one_required',
                '',
                new Request([], [
                    'file_key' => $fileKey,
                    'file_structure' => [
                        'header_row' => 1,
                        'first_column' => 0,
                        'first_product_row' => 2,
                        'unique_identifier_column' => 1,
                        'sheet_name' => 'Empty lines and columns',
                    ]
                ]),
            ],
            'An invalid file structure' => [
                'akeneo.tailored_import.validation.file_structure.first_product_row_should_be_after_header_row',
                '[file_structure][first_product_row]',
                new Request([], [
                    'file_key' => $fileKey,
                    'file_structure' => [
                        'header_row' => 1,
                        'first_column' => 0,
                        'first_product_row' => 1,
                        'unique_identifier_column' => 1,
                        'sheet_name' => 'Products',
                    ]
                ]),
            ],
        ];
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
