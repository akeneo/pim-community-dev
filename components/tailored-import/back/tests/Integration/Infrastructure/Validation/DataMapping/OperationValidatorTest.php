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

namespace Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation\DataMapping;

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Operations;
use Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

class OperationValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validOperation
     */
    public function test_it_does_not_build_violations_when_operation_is_valid(
        array $compatibleOperations,
        array $value,
    ): void {
        $violations = $this->getValidator()->validate($value, new Operations($compatibleOperations));

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidOperation
     */
    public function test_it_build_violations_when_operation_is_invalid(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        array $compatibleOperations,
        array $value,
    ): void {
        $violations = $this->getValidator()->validate($value, new Operations($compatibleOperations));

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validOperation(): array
    {
        return [
            'an supported operation' => [
                ['clean_html_tags'],
                [
                    [
                        'type' => 'clean_html_tags'
                    ],
                ],
            ],
            'empty operations' => [
                ['clean_html_tags'],
                [],
            ],
        ];
    }

    public function invalidOperation(): array
    {
        return [
            'an unsupported operation' => [
                'akeneo.tailored_import.validation.operations.incompatible_operation_type',
                '[0][type]',
                [],
                [
                    [
                        'type' => 'clean_html_tags'
                    ],
                ]
            ],
            'an non existent operation' => [
                'akeneo.tailored_import.validation.operations.operation_type_does_not_exist',
                '[0][type]',
                ['non_existent_operation'],
                [
                    [
                        'type' => 'non_existent_operation'
                    ],
                ]
            ],
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
