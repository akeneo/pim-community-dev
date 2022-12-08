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

use Akeneo\Platform\JobAutomation\Infrastructure\Validation\Storage\AmazonS3\AmazonS3Storage;
use AkeneoTest\Platform\Acceptance\ImportExport\Infrastructure\Validation\AbstractValidationTest;

class ValidateAmazonS3StorageTest extends AbstractValidationTest
{
    /**
     * @dataProvider validAmazonS3Storage
     */
    public function test_it_does_not_build_violations_when_amazon_s3_storage_is_valid(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new AmazonS3Storage(['xlsx', 'xls']));

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidAmazonS3Storage
     */
    public function test_it_builds_violations_when_amazon_s3_storage_is_invalid(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        array $value,
    ): void {
        $violations = $this->getValidator()->validate($value, new AmazonS3Storage(['xlsx', 'xls']));

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validAmazonS3Storage(): array
    {
        return [
            'valid amazon s3 storage' => [
                [
                    'type' => 'amazon_s3',
                    'region' => 'eu-west-3',
                    'bucket' => 'a_bucket',
                    'key' => 'a_key',
                    'secret' => 'a_secret',
                    'file_path' => '/tmp/products.xlsx',
                ],
            ],
        ];
    }

    public function invalidAmazonS3Storage(): array
    {
        return [
            'invalid storage type' => [
                'This value should be equal to "amazon_s3".',
                '[type]',
                [
                    'type' => 'invalid',
                    'region' => 'eu-west-3',
                    'bucket' => 'a_bucket',
                    'key' => 'a_key',
                    'secret' => 'a_secret',
                    'file_path' => '/tmp/products.xlsx',
                ],
            ],
            'amazon s3 storage without file_path' => [
                'This field is missing.',
                '[file_path]',
                [
                    'type' => 'amazon_s3',
                    'region' => 'eu-west-3',
                    'bucket' => 'a_bucket',
                    'key' => 'a_key',
                    'secret' => 'a_secret',
                ],
            ],
            'amazon s3 storage without region' => [
                'This field is missing.',
                '[region]',
                [
                    'type' => 'amazon_s3',
                    'bucket' => 'a_bucket',
                    'key' => 'a_key',
                    'secret' => 'a_secret',
                    'file_path' => '/tmp/products.xlsx',
                ],
            ],
            'amazon s3 storage with blank region' => [
                'This value should not be blank.',
                '[region]',
                [
                    'type' => 'amazon_s3',
                    'region' => '',
                    'bucket' => 'a_bucket',
                    'key' => 'a_key',
                    'secret' => 'a_secret',
                    'file_path' => '/tmp/products.xlsx',
                ],
            ],
            'amazon s3 storage without bucket' => [
                'This field is missing.',
                '[bucket]',
                [
                    'type' => 'amazon_s3',
                    'region' => 'eu-west-3',
                    'key' => 'a_key',
                    'secret' => 'a_secret',
                    'file_path' => '/tmp/products.xlsx',
                ],
            ],
            'amazon s3 storage with blank bucket' => [
                'This value should not be blank.',
                '[bucket]',
                [
                    'type' => 'amazon_s3',
                    'region' => 'eu-west-3',
                    'bucket' => '',
                    'key' => 'a_key',
                    'secret' => 'a_secret',
                    'file_path' => '/tmp/products.xlsx',
                ],
            ],
            'amazon s3 storage without key' => [
                'This field is missing.',
                '[key]',
                [
                    'type' => 'amazon_s3',
                    'region' => 'eu-west-3',
                    'bucket' => 'a_bucket',
                    'secret' => 'a_secret',
                    'file_path' => '/tmp/products.xlsx',
                ],
            ],
            'amazon s3 storage with blank key' => [
                'This value should not be blank.',
                '[key]',
                [
                    'type' => 'amazon_s3',
                    'region' => 'eu-west-3',
                    'bucket' => 'a_bucket',
                    'key' => '',
                    'secret' => 'a_secret',
                    'file_path' => '/tmp/products.xlsx',
                ],
            ],
            'amazon s3 storage without secret' => [
                'This field is missing.',
                '[secret]',
                [
                    'type' => 'amazon_s3',
                    'region' => 'eu-west-3',
                    'bucket' => 'a_bucket',
                    'key' => 'a_key',
                    'file_path' => '/tmp/products.xlsx',
                ],
            ],
            'sftp storage with blank secret' => [
                'This value should not be blank.',
                '[secret]',
                [
                    'type' => 'amazon_s3',
                    'region' => 'eu-west-3',
                    'bucket' => 'a_bucket',
                    'key' => 'a_key',
                    'secret' => '',
                    'file_path' => '/tmp/products.xlsx',
                ],
            ],
            'amazon s3 storage with additional fields' => [
                'This field was not expected.',
                '[additional]',
                [
                    'type' => 'amazon_s3',
                    'region' => 'eu-west-3',
                    'bucket' => 'a_bucket',
                    'key' => 'a_key',
                    'secret' => 'a_secret',
                    'file_path' => '/tmp/products.xlsx',
                    'additional' => 'invalid',
                ],
            ],
        ];
    }
}
