<?php

namespace Akeneo\Platform\JobAutomation\Test\Acceptance\Infrastructure\Validation\Storage;

use Akeneo\Platform\JobAutomation\Infrastructure\Validation\Storage\Local\ManualStorage;
use Akeneo\Platform\JobAutomation\Test\Acceptance\Infrastructure\Validation\AbstractValidationTest;

class ValidateLocalStorageTest extends AbstractValidationTest
{
    /**
     * @dataProvider validLocalStorage
     */
    public function testItDoesNotBuildViolationsWhenLocalStorageAreValid(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new ManualStorage(['xlsx', 'xls']));

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
        $violations = $this->getValidator()->validate($value, new ManualStorage(['xlsx', 'xls']));

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validLocalStorage(): array
    {
        return [
            'valid none storage' => [
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
