<?php

namespace AkeneoTest\Platform\Acceptance\ImportExport\Infrastructure\Validation\Storage;

use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation\Storage\None\NoneStorage;
use AkeneoTest\Platform\Acceptance\ImportExport\Infrastructure\Validation\AbstractValidationTest;

class ValidateNoneStorageTest extends AbstractValidationTest
{
    /**
     * @dataProvider validNoneStorage
     */
    public function test_it_does_not_build_violations_when_none_storage_are_valid(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new NoneStorage([]));

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidNoneStorage
     */
    public function test_it_build_violations_when_none_storage_are_invalid(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        array $value,
    ): void {
        $violations = $this->getValidator()->validate($value, new NoneStorage([]));

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validNoneStorage(): array
    {
        return [
            'valid none storage' => [
                [
                    'type' => 'none',
                ],
            ],
            'a storage with null file_path' => [
                [
                    'type' => 'none',
                    'file_path' => null,
                ],
            ]
        ];
    }

    public function invalidNoneStorage(): array
    {
        return [
            'invalid storage type' => [
                'This value should be equal to "none".',
                '[type]',
                [
                    'type' => 'invalid',
                ],
            ],
            'none storage with additional fields' => [
                'This field was not expected.',
                '[additional]',
                [
                    'type' => 'none',
                    'additional' => 'invalid',
                ],
            ],
        ];
    }
}
