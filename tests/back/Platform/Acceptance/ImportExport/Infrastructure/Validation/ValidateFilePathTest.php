<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Acceptance\ImportExport\Infrastructure\Validation;

use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation\FilePath;

class ValidateFilePathTest extends AbstractValidationTest
{
    /**
     * @dataProvider validFilePath
     */
    public function test_it_does_not_build_violations_when_file_path_are_valid(mixed $value): void
    {
        $violations = $this->getValidator()->validate($value, new FilePath(['xlsx', 'xls']));

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidFilePath
     */
    public function test_it_build_violations_when_file_path_are_invalid(
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
            'a null file path' => [null],
        ];
    }

    public function invalidFilePath(): array
    {
        return [
            'file path with bad extension' => [
                'pim_import_export.form.job_instance.validation.file_path.unsupported_extension',
                '',
                '/tmp/file.csv',
            ],
            'file path with non breaking space' => [
                'pim_import_export.form.job_instance.validation.file_path.non_printable_filepath',
                '',
                '/tmp/File​with​non​breaking​space.xlsx',
            ],
            'file path with soft hyphen' => [
                'pim_import_export.form.job_instance.validation.file_path.non_printable_filepath',
                '',
                '/tmp/File­with­soft­hyphen.xlsx',
            ]
        ];
    }
}
