<?php

namespace AkeneoTest\Platform\Acceptance\ImportExport\Infrastructure\Validation;

use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation\Storage;

class ValidateStorageTest extends AbstractValidationTest
{
    /**
     * @dataProvider validStorage
     */
    public function testItDoesNotBuildViolationsWhenStorageIsValid(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new Storage(['xlsx', 'xls']));

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidStorage
     */
    public function testItBuildViolationsWhenStorageIsInvalid(
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
                'akeneo.job_automation.validation.storage.unavailable_type',
                '[type]',
                [
                    'type' => 'invalid',
                ],
            ],
        ];
    }
}
