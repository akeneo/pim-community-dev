<?php

namespace Akeneo\Platform\JobAutomation\Test\Acceptance\Infrastructure\Validation;

use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation\Storage;
use Akeneo\Platform\JobAutomation\Infrastructure\Validation\Storage\Sftp\SftpStorage;
use AkeneoTest\Platform\Acceptance\ImportExport\Infrastructure\Validation\AbstractValidationTest;

class ValidateStorageTest extends AbstractValidationTest
{
    /**
     * @dataProvider validSftpStorage
     */
    public function test_it_does_not_build_violations_when_sftp_storage_is_valid(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new Storage(['xlsx', 'xls']));

        $this->assertNoViolation($violations);
    }

    public function validSftpStorage(): array
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
                    'username' => 'ziggy',
                    'password' => 'MySecretPassword',
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
            'akeneo.job_automation.validation.storage.unavailable_type',
            '[type]',
            $violations
        );
    }
}
