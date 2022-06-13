<?php

namespace AkeneoTest\Platform\Acceptance\ImportExport\Infrastructure\Validation\Storage;

use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation\Storage\ManualUpload\ManualUploadStorage;
use AkeneoTest\Platform\Acceptance\ImportExport\Infrastructure\Validation\AbstractValidationTest;

class ValidateManualStorageTest extends AbstractValidationTest
{
    /**
     * @dataProvider validManualStorage
     */
    public function testItDoesNotBuildViolationsWhenManualStorageAreValid(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new ManualUploadStorage(['xlsx', 'xls']));

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidManualStorage
     */
    public function testItBuildViolationsWhenManualStorageAreInvalid(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        array $value,
    ): void {
        $violations = $this->getValidator()->validate($value, new ManualUploadStorage(['xlsx', 'xls']));

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validManualStorage(): array
    {
        return [
            'valid manual storage' => [
                [
                    'type' => 'manual_upload',
                    'file_path' => '/tmp/products.xlsx',
                ],
            ],
        ];
    }

    public function invalidManualStorage(): array
    {
        return [
            'invalid storage type' => [
                'This value should be equal to "manual_upload".',
                '[type]',
                [
                    'type' => 'invalid',
                ],
            ],
            'manual storage without file_path' => [
                'This field is missing.',
                '[file_path]',
                [
                    'type' => 'manual_upload',
                ],
            ],
            'manual storage with additional fields' => [
                'This field was not expected.',
                '[additional]',
                [
                    'type' => 'manual_upload',
                    'file_path' => '/tmp/products.xlsx',
                    'additional' => 'invalid',
                ],
            ],
        ];
    }
}
