<?php

namespace Akeneo\Platform\JobAutomation\Test\Acceptance\Infrastructure\Validation;

use Akeneo\Platform\JobAutomation\Infrastructure\Validation\FilePath;

class ValidateFilePathTest extends AbstractValidationTest
{
    /**
     * @dataProvider validFilePath
     */
    public function testItDoesNotBuildViolationsWhenFilePathAreValid(string $value): void
    {
        $violations = $this->getValidator()->validate($value, new FilePath(['xlsx', 'xls']));

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidFilePath
     */
    public function testItBuildViolationsWhenFilePathAreInvalid(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        string $value,
    ): void {
        $violations = $this->getValidator()->validate($value, new FilePath(['xlsx', 'xls']));

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validFilePath(): array
    {
        return [
            'valid file path' => [
                '/tmp/file.xlsx',
            ],
        ];
    }

    public function invalidFilePath(): array
    {
        return [
            'blank file path type' => [
                'This value should not be blank.',
                '',
                '',
            ],
            'file path with bad extension' => [
                'akeneo.job_automation.validation.file_path.bad_extension',
                '',
                '/tmp/file.csv',
            ],
        ];
    }
}
