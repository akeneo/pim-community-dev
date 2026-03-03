<?php

namespace AkeneoTest\Platform\Acceptance\ImportExport\Infrastructure\Validation;

use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation\Storage;

class ValidateStorageTest extends AbstractValidationTest
{
    /**
     * @dataProvider validStorage
     */
    public function test_it_does_not_build_violations_when_storage_is_valid(array $value): void
    {
        $this->get('feature_flags')->enable('import_export_local_storage');
        $violations = $this->getValidator()->validate($value, new Storage(['xlsx', 'xls']));

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidStorage
     */
    public function test_it_build_violations_when_storage_is_invalid(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        array $value,
    ): void {
        $violations = $this->getValidator()->validate($value, new Storage(['xlsx', 'xls']));

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validStorage(): array
    {
        return [
            'valid none storage' => [
                [
                    'type' => 'none',
                ],
            ],
            'valid local storage' => [
                [
                    'type' => 'local',
                    'file_path' => '/tmp/file.xlsx',
                ],
            ],
        ];
    }

    public function invalidStorage(): array
    {
        return [
            'invalid storage type' => [
                'pim_import_export.form.job_instance.validation.storage.unavailable_type',
                '[type]',
                [
                    'type' => 'invalid',
                ],
            ],
        ];
    }
}
