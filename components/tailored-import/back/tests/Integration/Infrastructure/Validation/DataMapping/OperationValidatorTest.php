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
        array $requiredOperations,
        array $value,
    ): void {
        $violations = $this->getValidator()->validate($value, new Operations($compatibleOperations, $requiredOperations));

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidOperation
     */
    public function test_it_build_violations_when_operation_is_invalid(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        array $compatibleOperations,
        array $requiredOperations,
        array $value,
    ): void {
        $violations = $this->getValidator()->validate($value, new Operations($compatibleOperations, $requiredOperations));

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validOperation(): array
    {
        return [
            'a supported operation' => [
                ['clean_html_tags'],
                [],
                [
                    [
                        'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d6',
                        'type' => 'clean_html_tags'
                    ],
                ],
            ],
            'a supported and required operation' => [
                ['boolean_replacement'],
                ['boolean_replacement'],
                [
                    [
                        'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d6',
                        'type' => 'boolean_replacement',
                        'mapping' => [
                            'true' => ['Yes'],
                            'false' => ['No'],
                        ],
                    ],
                ],
            ],
            'an empty operation' => [
                ['clean_html_tags'],
                [],
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
                [],
                [
                    [
                        'type' => 'non_existent_operation'
                    ],
                ]
            ],
            'a missing required operation' => [
                'akeneo.tailored_import.validation.operations.missing_required_operation',
                '',
                ['split', 'boolean_replacement'],
                ['boolean_replacement'],
                [
                    [
                        'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d6',
                        'type' => 'split',
                        'separator' => ';',
                    ],
                ]
            ]
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
