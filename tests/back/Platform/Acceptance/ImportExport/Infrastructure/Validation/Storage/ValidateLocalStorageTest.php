<?php

namespace AkeneoTest\Platform\Acceptance\ImportExport\Infrastructure\Validation\Storage;

use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation\Storage\Local\LocalStorage;
use AkeneoTest\Platform\Acceptance\ImportExport\Infrastructure\Validation\AbstractValidationTest;

class ValidateLocalStorageTest extends AbstractValidationTest
{
    /**
     * @dataProvider validLocalStorage
     */
    public function testItDoesNotBuildViolationsWhenLocalStorageAreValid(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new LocalStorage(['xlsx', 'xls']));

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidLocalStorage
     */
    public function testItBuildViolationsWhenLocalStorageAreInvalid(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        array $value,
    ): void {
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
        ];
    }

    public function invalidLocalStorage(): array
    {
        return [
            'invalid storage type' => [
                'This value should be equal to "local".',
                '[type]',
                [
                    'type' => 'invalid',
                ],
            ],
            'local storage without file_path' => [
                'This field is missing.',
                '[file_path]',
                [
                    'type' => 'local',
                ],
            ],
            'local storage with additional fields' => [
                'This field was not expected.',
                '[additional]',
                [
                    'type' => 'local',
                    'file_path' => '/tmp/products.xlsx',
                    'additional' => 'invalid',
                ],
            ],
        ];
    }
}
