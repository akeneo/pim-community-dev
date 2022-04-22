<?php

namespace Akeneo\Platform\JobAutomation\Test\Acceptance\Infrastructure\Validation\Storage;

use Akeneo\Platform\JobAutomation\Infrastructure\Validation\Storage\Sftp\SftpStorage;
use Akeneo\Platform\JobAutomation\Test\Acceptance\Infrastructure\Validation\AbstractValidationTest;

class ValidateSftpStorageTest extends AbstractValidationTest
{
    /**
     * @dataProvider validSftpStorage
     */
    public function testItDoesNotBuildViolationsWhenSftpStorageAreValid(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new SftpStorage());

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidSftpStorage
     */
    public function testItBuildViolationsWhenSftpStorageAreInvalid(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        array $value,
    ): void {
        $violations = $this->getValidator()->validate($value, new SftpStorage());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validSftpStorage(): array
    {
        return [
            'valid sftp storage with url host' => [
                [
                    'type' => 'sftp',
                    'file_path' => '/tmp/products.xlsx',
                    'host' => 'example.com',
                    'port' => 22,
                    'username' => 'ziggy',
                    'password' => 'MySecretPassword',
                ],
            ],
            'valid sftp storage with ip host' => [
                [
                    'type' => 'sftp',
                    'file_path' => '/tmp/products.xlsx',
                    'host' => '192.168.0.98',
                    'port' => 22,
                    'username' => 'ziggy',
                    'password' => 'MySecretPassword',
                ],
            ],
        ];
    }

    public function invalidSftpStorage(): array
    {
        return [
            'invalid storage type' => [
                'This value should be equal to "sftp".',
                '[type]',
                [
                    'type' => 'invalid',
                    'file_path' => '/tmp/products.xlsx',
                    'host' => 'example.com',
                    'port' => 22,
                    'username' => 'ziggy',
                    'password' => 'MySecretPassword',
                ],
            ],
            'sftp storage without file_path' => [
                'This field is missing.',
                '[file_path]',
                [
                    'type' => 'sftp',
                    'host' => 'example.com',
                    'port' => 22,
                    'username' => 'ziggy',
                    'password' => 'MySecretPassword',
                ],
            ],
            'sftp storage without host' => [
                'This field is missing.',
                '[host]',
                [
                    'type' => 'sftp',
                    'file_path' => '/tmp/products.xlsx',
                    'port' => 22,
                    'username' => 'ziggy',
                    'password' => 'MySecretPassword',
                ],
            ],
            'sftp storage with blank host' => [
                'This value should not be blank.',
                '[host]',
                [
                    'type' => 'sftp',
                    'file_path' => '/tmp/products.xlsx',
                    'host' => '',
                    'port' => 22,
                    'username' => 'ziggy',
                    'password' => 'MySecretPassword',
                ],
            ],
            'sftp storage with invalid host' => [
                'This value is not a valid hostname.',
                '[host]',
                [
                    'type' => 'sftp',
                    'file_path' => '/tmp/products.xlsx',
                    'host' => 'invalid',
                    'username' => 'ziggy',
                    'password' => 'MySecretPassword',
                ],
            ],
            'sftp storage without port' => [
                'This field is missing.',
                '[port]',
                [
                    'type' => 'sftp',
                    'file_path' => '/tmp/products.xlsx',
                    'host' => 'example.com',
                    'username' => 'ziggy',
                    'password' => 'MySecretPassword',
                ],
            ],
            'sftp storage with invalid port' => [
                'This value should be greater than or equal to 1.',
                '[port]',
                [
                    'type' => 'sftp',
                    'file_path' => '/tmp/products.xlsx',
                    'host' => 'example.com',
                    'port' => 0,
                    'username' => 'ziggy',
                    'password' => 'MySecretPassword',
                ],
            ],
            'sftp storage without username' => [
                'This field is missing.',
                '[username]',
                [
                    'type' => 'sftp',
                    'file_path' => '/tmp/products.xlsx',
                    'host' => 'example.com',
                    'port' => 22,
                    'password' => 'MySecretPassword',
                ],
            ],
            'sftp storage with blank username' => [
                'This value should not be blank.',
                '[username]',
                [
                    'type' => 'sftp',
                    'file_path' => '/tmp/products.xlsx',
                    'host' => 'example.com',
                    'port' => 22,
                    'username' => '',
                    'password' => 'MySecretPassword',
                ],
            ],
            'sftp storage without password' => [
                'This field is missing.',
                '[password]',
                [
                    'type' => 'sftp',
                    'file_path' => '/tmp/products.xlsx',
                    'host' => 'example.com',
                    'port' => 22,
                    'username' => 'ziggy',
                ],
            ],
            'sftp storage with blank password' => [
                'This value should not be blank.',
                '[password]',
                [
                    'type' => 'sftp',
                    'file_path' => '/tmp/products.xlsx',
                    'host' => 'example.com',
                    'port' => 22,
                    'username' => 'ziggy',
                    'password' => '',
                ],
            ],
            'sftp storage with additional fields' => [
                'This field was not expected.',
                '[additional]',
                [
                    'type' => 'sftp',
                    'file_path' => 'test.xlsx',
                    'host' => 'example.com',
                    'port' => 22,
                    'username' => 'ziggy',
                    'password' => 'MySecretPassword',
                    'additional' => 'invalid',
                ],
            ],
        ];
    }
}
