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

use Akeneo\Platform\JobAutomation\Infrastructure\Validation\Storage\MicrosoftAzure\MicrosoftAzureStorage;
use AkeneoTest\Platform\Acceptance\ImportExport\Infrastructure\Validation\AbstractValidationTest;

class ValidateMicrosoftAzureStorageTest extends AbstractValidationTest
{
    /**
     * @dataProvider validMicrosoftAzureStorage
     */
    public function test_it_does_not_build_violations_when_microsoft_azure_storage_is_valid(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new MicrosoftAzureStorage(['xlsx', 'xls']));

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidMicrosoftAzureStorage
     */
    public function test_it_builds_violations_when_microsoft_azure_storage_is_invalid(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        array $value,
    ): void {
        $violations = $this->getValidator()->validate($value, new MicrosoftAzureStorage(['xlsx', 'xls']));

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validMicrosoftAzureStorage(): array
    {
        return [
            'valid microsoft azure storage' => [
                [
                    'type' => 'microsoft_azure',
                    'connection_string' => 'a_connection_string',
                    'container_name' => 'a_container_name',
                    'file_path' => '/tmp/products.xlsx',
                ],
            ],
        ];
    }

    public function invalidMicrosoftAzureStorage(): array
    {
        return [
            'invalid storage type' => [
                'This value should be equal to "microsoft_azure".',
                '[type]',
                [
                    'type' => 'invalid',
                    'connection_string' => 'a_connection_string',
                    'container_name' => 'a_container_name',
                    'file_path' => '/tmp/products.xlsx',
                ],
            ],
            'microsoft azure storage without file path' => [
                'This field is missing.',
                '[file_path]',
                [
                    'type' => 'microsoft_azure',
                    'connection_string' => 'a_connection_string',
                    'container_name' => 'a_container_name',
                ],
            ],
            'microsoft azure storage without container name' => [
                'This field is missing.',
                '[container_name]',
                [
                    'type' => 'microsoft_azure',
                    'connection_string' => 'a_connection_string',
                    'file_path' => '/tmp/products.xlsx',
                ],
            ],
            'microsoft azure storage without connection string' => [
                'This field is missing.',
                '[connection_string]',
                [
                    'type' => 'microsoft_azure',
                    'container_name' => 'a_container_name',
                    'file_path' => '/tmp/products.xlsx',
                ],
            ],
            'microsoft azure storage with blank container name' => [
                'This value should not be blank.',
                '[container_name]',
                [
                    'type' => 'microsoft_azure',
                    'connection_string' => 'a_connection_string',
                    'container_name' => '',
                    'file_path' => '/tmp/products.xlsx',
                ],
            ],
            'microsoft azure storage with blank connection string' => [
                'This value should not be blank.',
                '[connection_string]',
                [
                    'type' => 'microsoft_azure',
                    'connection_string' => '',
                    'container_name' => 'a_container_name',
                    'file_path' => '/tmp/products.xlsx',
                ],
            ],
        ];
    }
}
