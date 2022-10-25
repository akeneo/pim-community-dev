<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\JobAutomation\Test\Acceptance\Infrastructure\Validation\Storage;

use Akeneo\Platform\JobAutomation\Infrastructure\Validation\Storage\Sftp\SftpStorage;
use AkeneoTest\Platform\Acceptance\ImportExport\Infrastructure\Validation\AbstractValidationTest;

class ValidateSftpStorageTest extends AbstractValidationTest
{
    private const VALID_SHA512_FINGERPRINT = '6f:0a:fc:c7:59:32:0d:7f:78:1b:76:24:a9:51:a4:f9:c3:35:4b:7c:e6:0d:28:d4:cd:5e:5d:62:51:85:e4:93:60:f4:ae:70:a1:ac:ba:1c:92:c7:f4:4a:55:3b:7e:ac:c3:14:0f:4f:d2:b7:e7:87:d7:4f:e2:6d:1e:ab:0c:92';
    private const VALID_MD5_FINGERPRINT = '6f:0a:fc:c7:59:32:0d:7f:78:1b:76:24:a9:51:a4:f9';

    /**
     * @dataProvider validSftpStorage
     */
    public function test_it_does_not_build_violations_when_sftp_storage_is_valid(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new SftpStorage(['xlsx', 'xls']));

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidSftpStorage
     */
    public function test_it_builds_violations_when_sftp_storage_is_invalid(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        array $value,
    ): void {
        $violations = $this->getValidator()->validate($value, new SftpStorage(['xlsx', 'xls']));

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
                    'login_type' => 'password',
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
                    'login_type' => 'password',
                    'username' => 'ziggy',
                    'password' => 'MySecretPassword',
                ],
            ],
            'valid sftp storage with SHA-512 fingerprint' => [
                [
                    'type' => 'sftp',
                    'file_path' => '/tmp/products.xlsx',
                    'host' => '192.168.0.98',
                    'fingerprint' => self::VALID_SHA512_FINGERPRINT,
                    'port' => 22,
                    'username' => 'ziggy',
                    'password' => 'MySecretPassword',
                ],
            ],
            'valid sftp storage with MD5 fingerprint' => [
                [
                    'type' => 'sftp',
                    'file_path' => '/tmp/products.xlsx',
                    'host' => '192.168.0.98',
                    'fingerprint' => self::VALID_MD5_FINGERPRINT,
                    'port' => 22,
                    'username' => 'ziggy',
                    'password' => 'MySecretPassword',
                ],
            ],
            'valid sftp storage with private key login type' => [
                [
                    'type' => 'sftp',
                    'file_path' => '/tmp/products.xlsx',
                    'host' => '192.168.0.98',
                    'port' => 22,
                    'login_type' => 'private_key',
                    'username' => 'ziggy',
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
                    'login_type' => 'password',
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
                    'login_type' => 'password',
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
                    'login_type' => 'password',
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
                    'login_type' => 'password',
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
                    'login_type' => 'password',
                    'username' => 'ziggy',
                    'password' => 'MySecretPassword',
                ],
            ],
            'sftp storage with invalid fingerprint' => [
                'pim_import_export.form.job_instance.validation.fingerprint.invalid_encoding',
                '[fingerprint]',
                [
                    'type' => 'sftp',
                    'file_path' => '/tmp/products.xlsx',
                    'host' => 'test.com',
                    'fingerprint' => 'invalid fingerprint',
                    'username' => 'ziggy',
                    'password' => 'MySecretPassword',
                ],
            ],
            'sftp storage with host with user and password inside' => [
                'This value is not a valid hostname.',
                '[host]',
                [
                    'type' => 'sftp',
                    'file_path' => '/tmp/products.xlsx',
                    'host' => 'ziggy:pass@example.com',
                    'login_type' => 'password',
                    'username' => 'ziggy',
                    'password' => 'MySecretPassword',
                ],
            ],
            'sftp storage with url host with trailing port' => [
                'This value is not a valid hostname.',
                '[host]',
                [
                    'type' => 'sftp',
                    'file_path' => '/tmp/products.xlsx',
                    'host' => 'example.com:8080',
                    'login_type' => 'password',
                    'username' => 'ziggy',
                    'password' => 'MySecretPassword',
                ],
            ],
            'sftp storage with ip host with trailing port' => [
                'This value is not a valid hostname.',
                '[host]',
                [
                    'type' => 'sftp',
                    'file_path' => '/tmp/products.xlsx',
                    'host' => '192.168.0.98:8080',
                    'login_type' => 'password',
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
                    'login_type' => 'password',
                    'username' => 'ziggy',
                    'password' => 'MySecretPassword',
                ],
            ],
            'sftp storage with blank port' => [
                'This value should be greater than or equal to 1.',
                '[port]',
                [
                    'type' => 'sftp',
                    'file_path' => '/tmp/products.xlsx',
                    'host' => 'example.com',
                    'port' => '',
                    'login_type' => 'password',
                    'username' => 'ziggy',
                    'password' => 'MySecretPassword',
                ],
            ],
            'sftp storage with port lesser than 1' => [
                'This value should be greater than or equal to 1.',
                '[port]',
                [
                    'type' => 'sftp',
                    'file_path' => '/tmp/products.xlsx',
                    'host' => 'example.com',
                    'port' => 0,
                    'login_type' => 'password',
                    'username' => 'ziggy',
                    'password' => 'MySecretPassword',
                ],
            ],
            'sftp storage with port greater than 65535' => [
                'This value should be less than or equal to 65535.',
                '[port]',
                [
                    'type' => 'sftp',
                    'file_path' => '/tmp/products.xlsx',
                    'host' => 'example.com',
                    'port' => 65536,
                    'login_type' => 'password',
                    'username' => 'ziggy',
                    'password' => 'MySecretPassword',
                ],
            ],
            'sftp storage with invalid login type' => [
                'The value you selected is not a valid choice.',
                '[login_type]',
                [
                    'type' => 'sftp',
                    'file_path' => '/tmp/products.xlsx',
                    'host' => '192.168.0.98:8080',
                    'login_type' => 'invalid',
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
                    'login_type' => 'password',
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
                    'login_type' => 'password',
                    'username' => '',
                    'password' => 'MySecretPassword',
                ],
            ],
            'sftp storage without password' => [
                'This value should not be blank.',
                '[password]',
                [
                    'type' => 'sftp',
                    'file_path' => '/tmp/products.xlsx',
                    'host' => 'example.com',
                    'port' => 22,
                    'login_type' => 'password',
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
                    'login_type' => 'password',
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
                    'login_type' => 'password',
                    'username' => 'ziggy',
                    'password' => 'MySecretPassword',
                    'additional' => 'invalid',
                ],
            ],
        ];
    }
}
