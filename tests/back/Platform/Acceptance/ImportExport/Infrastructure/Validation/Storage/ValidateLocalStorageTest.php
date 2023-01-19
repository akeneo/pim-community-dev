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
        $violations = $this->getValidator()->validate($value, new LocalStorage(['xlsx', 'xls']));

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidLocalStorage
     */
    public function test_it_build_violations_when_local_storage_are_invalid(
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
                'This value should be equal to "local".',
                '[type]',
                [
                    'type' => 'invalid',
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
