<?php

namespace Akeneo\Platform\JobAutomation\Test\Acceptance\Infrastructure\Validation\Storage;

use Akeneo\Platform\JobAutomation\Infrastructure\Validation\Storage\ManualUpload\ManualUploadStorage;
use Akeneo\Platform\JobAutomation\Test\Acceptance\Infrastructure\Validation\AbstractValidationTest;

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
                    'type' => 'manual',
                    'file_path' => '/tmp/products.xlsx',
                ],
            ],
        ];
    }

    public function invalidManualStorage(): array
    {
        return [
            'invalid storage type' => [
                'This value should be equal to "manual".',
                '[type]',
                [
                    'type' => 'invalid',
                ],
            ],
            'manual storage without file_path' => [
                'This field is missing.',
                '[file_path]',
                [
                    'type' => 'manual',
                ],
            ],
            'manual storage with additional fields' => [
                'This field was not expected.',
                '[additional]',
                [
                    'type' => 'manual',
                    'file_path' => '/tmp/products.xlsx',
                    'additional' => 'invalid',
                ],
            ],
        ];
    }
}
