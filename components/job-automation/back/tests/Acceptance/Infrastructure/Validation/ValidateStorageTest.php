<?php

namespace Akeneo\Platform\JobAutomation\Test\Acceptance\Infrastructure\Validation;

use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation\Storage;
use AkeneoTest\Platform\Acceptance\ImportExport\Infrastructure\Validation\AbstractValidationTest;

class ValidateStorageTest extends AbstractValidationTest
{
    /**
     * @dataProvider validStorage
     */
    public function test_it_does_not_build_violations_when_storage_is_valid(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new Storage(['xlsx', 'xls']));

        $this->assertNoViolation($violations);
    }

    public function validStorage(): array
    {
        return [
            'Valid local storage configuration' => [
                [
                    'type' => 'local',
                    'file_path' => '/tmp/file.xlsx',
                ],
            ],
            'Valid sftp storage configuration' => [
                [
                    'type' => 'sftp',
                    'file_path' => '/tmp/file.xlsx',
                    'host' => 'example.com',
                    'port' => 22,
                    'login_type' => 'password',
                    'username' => 'ziggy',
                    'password' => 'MySecretPassword',
                ],
            ],
            'Valid amazon s3 storage configuration' => [
                [
                    'type' => 'amazon_s3',
                    'file_path' => '/tmp/file.xlsx',
                    'region' => 'eu-west-1',
                    'bucket' => 'a_bucket',
                    'key' => 'ziggy',
                    'secret' => 'MySecret',
                ],
            ],
        ];
    }

    public function test_it_build_violations_when_storage_is_invalid(): void
    {
        $violations = $this->getValidator()->validate(
            [
                'type' => 'invalid',
            ],
            new Storage(['xlsx', 'xls'])
        );

        $this->assertHasValidationError(
            'pim_import_export.form.job_instance.validation.storage.unavailable_type',
            '[type]',
            $violations
        );
    }
}
