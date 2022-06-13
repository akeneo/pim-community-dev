<?php

namespace Akeneo\Platform\JobAutomation\Test\Acceptance\Infrastructure\Validation;

use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation\Storage;
use AkeneoTest\Platform\Acceptance\ImportExport\Infrastructure\Validation\AbstractValidationTest;

class ValidateStorageTest extends AbstractValidationTest
{
    public function testItDoesNotBuildViolationsWhenStorageIsValid(): void
    {
        $violations = $this->getValidator()->validate(
            [
                'type' => 'sftp',
                'file_path' => '/tmp/file.xlsx',
                'host' => 'example.com',
                'port' => 22,
                'username' => 'ziggy',
                'password' => 'MySecretPassword',
            ],
            new Storage(['xlsx', 'xls'])
        );

        $this->assertNoViolation($violations);
    }

    public function testItBuildViolationsWhenStorageIsInvalid(): void
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
