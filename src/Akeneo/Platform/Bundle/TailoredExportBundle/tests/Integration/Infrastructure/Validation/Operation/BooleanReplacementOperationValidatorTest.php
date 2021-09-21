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

use Akeneo\Platform\TailoredExport\Infrastructure\Validation\Operation\BooleanReplacementOperationConstraint;
use Akeneo\Platform\TailoredExport\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

class BooleanReplacementOperationValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validOperation
     */
    public function test_it_does_not_build_violations_on_valid_operation(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new BooleanReplacementOperationConstraint());

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
        $violations = $this->getValidator()->validate($value, new BooleanReplacementOperationConstraint());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validOperation(): array
    {
        return [
            'a boolean replacement' => [
                [
                    'type' => 'replacement',
                    'mapping' => [
                        'true' => 'vrai',
                        'false' => 'faux',
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
                        'true' => 'vrai',
                        'false' => 'faux',
                    ],
                ],
            ],
            'too long true replacement' => [
                'akeneo.tailored_export.validation.max_length_reached',
                '[mapping][true]',
                [
                    'type' => 'replacement',
                    'mapping' => [
                        'true' => str_repeat('m', 256),
                        'false' => 'faux',
                    ],
                ],
            ],
            'too long false replacement' => [
                'akeneo.tailored_export.validation.max_length_reached',
                '[mapping][false]',
                [
                    'type' => 'replacement',
                    'mapping' => [
                        'true' => 'vrai',
                        'false' => str_repeat('m', 256),
                    ],
                ],
            ],
            'empty true replacement' => [
                'akeneo.tailored_export.validation.required',
                '[mapping][true]',
                [
                    'type' => 'replacement',
                    'mapping' => [
                        'true' => '',
                        'false' => 'faux',
                    ],
                ],
            ],
            'empty false replacement' => [
                'akeneo.tailored_export.validation.required',
                '[mapping][false]',
                [
                    'type' => 'replacement',
                    'mapping' => [
                        'true' => 'vrai',
                        'false' => '',
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
