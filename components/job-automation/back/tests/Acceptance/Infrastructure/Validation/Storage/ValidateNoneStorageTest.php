<?php

namespace Akeneo\Platform\JobAutomation\Test\Acceptance\Infrastructure\Validation\Storage;

use Akeneo\Platform\JobAutomation\Infrastructure\Validation\Storage\None\NoneStorage;
use Akeneo\Platform\JobAutomation\Test\Acceptance\Infrastructure\Validation\AbstractValidationTest;

class ValidateNoneStorageTest extends AbstractValidationTest
{
    /**
     * @dataProvider validNoneStorage
     */
    public function testItDoesNotBuildViolationsWhenNoneStorageAreValid(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new NoneStorage([]));

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidNoneStorage
     */
    public function testItBuildViolationsWhenNoneStorageAreInvalid(
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
