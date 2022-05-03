<?php

namespace Akeneo\Platform\JobAutomation\Test\Acceptance\Infrastructure\Validation;

use Akeneo\Platform\JobAutomation\Infrastructure\Validation\Storage;

class ValidateStorageTest extends AbstractValidationTest
{
    /**
     * @dataProvider validStorage
     */
    public function testItDoesNotBuildViolationsWhenStorageAreValid(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new Storage(['xlsx', 'xls']));

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidStorage
     */
    public function testItBuildViolationsWhenStorageAreInvalid(
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
            'valid sftp storage' => [
                [
                    'type' => 'sftp',
                    'file_path' => '/tmp/file.xlsx',
                    'host' => 'example.com',
                    'port' => 22,
                    'username' => 'ziggy',
                    'password' => 'MySecretPassword',
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
