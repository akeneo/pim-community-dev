<?php

namespace AkeneoTest\Platform\Acceptance\ImportExport\Infrastructure\Validation\Storage;

use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation\Storage\Local\LocalStorage;
use AkeneoTest\Platform\Acceptance\ImportExport\Infrastructure\Validation\AbstractValidationTest;

class ValidateLocalStorageTest extends AbstractValidationTest
{
    /**
     * @dataProvider validLocalStorage
     */
    public function test_it_does_not_build_violations_when_local_storage_are_valid(array $value): void
    {
        $this->get('feature_flags')->enable('import_export_local_storage');
        $violations = $this->getValidator()->validate($value, new LocalStorage(['xlsx', 'xls']));

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidLocalStorage
     */
    public function test_it_build_violations_when_local_storage_are_invalid(
        bool $importExportLocalStorageIsEnabled,
        string $expectedErrorMessage,
        string $expectedErrorPath,
        array $value,
    ): void {
        if ($importExportLocalStorageIsEnabled) {
            $this->get('feature_flags')->enable('import_export_local_storage');
        }

        $violations = $this->getValidator()->validate($value, new LocalStorage(['xlsx', 'xls']));

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validLocalStorage(): array
    {
        return [
            'valid local storage' => [
                [
                    'type' => 'local',
                    'file_path' => '/tmp/products.xlsx',
                ],
            ],
            'a storage with null file_path' => [
                [
                    'type' => 'local',
                    'file_path' => null,
                ],
            ]
        ];
    }

    public function invalidLocalStorage(): array
    {
        return [
            'invalid storage type' => [
                true,
                'This value should be equal to "local".',
                '[type]',
                [
                    'type' => 'invalid',
                ],
            ],
            'local storage with additional fields' => [
                true,
                'This field was not expected.',
                '[additional]',
                [
                    'type' => 'local',
                    'file_path' => '/tmp/products.xlsx',
                    'additional' => 'invalid',
                ],
            ],
            'local storage feature flag disabled' => [
                false,
                'pim_import_export.form.job_instance.validation.storage.local.unavailable',
                '[type]',
                [
                    'type' => 'local',
                    'file_path' => '/tmp/products.xlsx',
                    'additional' => 'invalid',
                ],
            ],
        ];
    }
}
