<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Test\Integration\Infrastructure\Validation\Operation;

use Akeneo\Platform\TailoredExport\Infrastructure\Validation\Operation\ReplacementOperationConstraint;
use Akeneo\Platform\TailoredExport\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

class ReplacementOperationValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validOperation
     */
    public function test_it_does_not_build_violations_on_valid_operation(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new ReplacementOperationConstraint());

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
        $violations = $this->getValidator()->validate($value, new ReplacementOperationConstraint());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validOperation(): array
    {
        return [
            'a boolean replacement' => [
                [
                    'type' => 'replacement',
                    'mapping' => [
                        'code_1' => 'replacement_value_1',
                        'code_2' => 'replacement_value_2',
                    ],
                ],
            ],
        ];
    }

    public function invalidOperation(): array
    {
        return [
            'invalid type' => [
                'This value should be equal to "replacement".',
                '[type]',
                [
                    'type' => 'invalid type',
                    'mapping' => [
                        'code_1' => 'replacement_value_1',
                        'code_2' => 'replacement_value_2',
                    ],
                ],
            ],
            'too long replacement' => [
                'akeneo.tailored_export.validation.max_length_reached',
                '[mapping][code_1]',
                [
                    'type' => 'replacement',
                    'mapping' => [
                        'code_1' => str_repeat('m', 256),
                        'code_2' => 'replacement_value_2',
                    ],
                ],
            ],
            'empty replacement' => [
                'akeneo.tailored_export.validation.required',
                '[mapping][code_2]',
                [
                    'type' => 'replacement',
                    'mapping' => [
                        'code_1' => 'replacement_value_1',
                        'code_2' => '',
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
