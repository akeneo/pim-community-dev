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

namespace Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation\SampleData;

use Akeneo\Platform\TailoredImport\Domain\Model\Filesystem\Storage;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\SampleData\SampleDataQuery;
use Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

final class SampleDataQueryValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validRequest
     */
    public function test_it_does_not_build_violations_when_request_is_valid(Request $value): void
    {
        $violations = $this->getValidator()->validate($value, new SampleDataQuery());

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidRequest
     */
    public function test_it_builds_violations_when_request_is_invalid(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        Request $value
    ): void {
        $violations = $this->getValidator()->validate($value, new SampleDataQuery());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validRequest(): array
    {
        $filePath = $this->storeFile();

        return [
            'valid request with one column index' => [
                new Request([
                    'file_key' => $filePath,
                    'column_indices' => ['1'],
                    'sheet_name' => 'Sheet1',
                    'product_line' => '1',
                ])
            ],
            'valid request with multiple column indices' => [
                new Request([
                    'file_key' => $filePath,
                    'column_indices' => ['1', '2'],
                    'sheet_name' => 'Sheet1',
                    'product_line' => '1',
                ])
            ],
        ];
    }

    public function invalidRequest(): array
    {
        return [
            'invalid request with wrong sheet name type' => [
                'This value should be of type {{ type }}.',
                '[sheet_name]',
                new Request([
                    'file_key' => 'e/e/d/d/eedd05148a6311b2bffe29eb1adc80c2cf6ad9ca_bigfile.xlsx',
                    'column_indices' => ['1', '2'],
                    'sheet_name' => false,
                    'product_line' => '1',
                ])
            ],
        ];
    }

    private function storeFile(): string
    {
        $uploadedFile = new UploadedFile(__DIR__ . '/../../../../Common/simple_import.xlsx', 'filename.xlsx');
        $file = $this->get('akeneo_file_storage.file_storage.file.file_storer')->store($uploadedFile, Storage::FILE_STORAGE_ALIAS);

        return $file->getKey();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
