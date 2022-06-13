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

namespace Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation\Operation;

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Operation\ReplacementOperation;
use Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

class ReplacementOperationValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validOperation
     */
    public function test_it_does_not_build_violations_on_valid_operation(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new ReplacementOperation());

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidOperation
     */
    public function test_it_builds_violations_on_invalid_operation(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        array $value
    ): void {
        $violations = $this->getValidator()->validate($value, new ReplacementOperation());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validOperation(): array
    {
        return [
            'a simple select replacement' => [
                [
                    'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d6',
                    'type' => 'simple_select_replacement',
                    'mapping' => [
                        'code_1' => ['replacement_value_1'],
                        'code_2' => ['replacement_value_2'],
                    ],
                ],
            ],
            'a category replacement' => [
                [
                    'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d7',
                    'type' => 'category_replacement',
                    'mapping' => [
                        'tshirt' => ['T SHIRT', 'Tee-shirt'],
                        'godasse' => ['SHOES', 'Chaussures'],
                    ],
                ],
            ],
        ];
    }

    public function invalidOperation(): array
    {
        return [
            'too long replacement' => [
                'akeneo.tailored_import.validation.max_length_reached',
                '[mapping][code_1][0]',
                [
                    'type' => 'simple_select_replacement',
                    'mapping' => [
                        'code_1' => [str_repeat('m', 256)],
                        'code_2' => ['replacement_value_2'],
                    ],
                ],
            ],
            'empty replacement' => [
                'akeneo.tailored_import.validation.required',
                '[mapping][code_2]',
                [
                    'type' => 'simple_select_replacement',
                    'mapping' => [
                        'code_1' => ['replacement_value_1'],
                        'code_2' => [],
                    ],
                ],
            ],
            'source values are not unique' => [
                'akeneo.tailored_import.validation.operation.replacement.source_values_should_be_unique',
                '[mapping][code_2]',
                [
                    'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d6',
                    'type' => 'simple_select_replacement',
                    'mapping' => [
                        'code_1' => ['replacement_value_1'],
                        'code_2' => ['replacement_value_2', 'replacement_value_1'],
                    ],
                ],
            ],
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
