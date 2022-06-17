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

namespace Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation\DataMapping\Operation;

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Operation\BooleanReplacementOperation;
use Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

final class BooleanReplacementOperationValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validOperation
     */
    public function test_it_does_not_build_violations_when_operation_is_valid(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new BooleanReplacementOperation());

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidOperation
     */
    public function test_it_build_violations_when_operation_is_invalid(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        array $value
    ): void {
        $violations = $this->getValidator()->validate($value, new BooleanReplacementOperation());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validOperation(): array
    {
        return [
            'a valid boolean replacement operation' => [
                [
                    'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d6',
                    'type' => 'boolean_replacement',
                    'mapping' => [
                        'true' => ['Yes'],
                        'false' => ['No'],
                    ],
                ],
            ],
        ];
    }

    public function invalidOperation(): array
    {
        return [
            'a boolean replacement with wrong uuid' => [
                'This is not a valid UUID.',
                '[uuid]',
                [
                    'uuid' => 'invalid',
                    'type' => 'boolean_replacement',
                    'mapping' => [
                        'true' => ['1'],
                        'false' => ['0'],
                    ],
                ],
            ],
            'a boolean replacement with wrong type' => [
                'This value should be equal to "boolean_replacement".',
                '[type]',
                [
                    'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d6',
                    'type' => 'invalid_operation',
                    'mapping' => [
                        'true' => ['1'],
                        'false' => ['0'],
                    ],
                ],
            ],
            'a boolean replacement with wrong mapping' => [
                'This value should be of type array|(Traversable&ArrayAccess).',
                '[mapping]',
                [
                    'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d6',
                    'type' => 'boolean_replacement',
                    'mapping' => 'invalid'
                ],
            ],
            'a boolean replacement with mapping missing true' => [
                'This field is missing.',
                '[mapping][true]',
                [
                    'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d6',
                    'type' => 'boolean_replacement',
                    'mapping' => [
                        'false' => ['0'],
                    ],
                ],
            ],
            'a boolean replacement with mapping missing false' => [
                'This field is missing.',
                '[mapping][false]',
                [
                    'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d6',
                    'type' => 'boolean_replacement',
                    'mapping' => [
                        'true' => ['1'],
                    ],
                ],
            ],
            'a boolean replacement with blank true in mapping' => [
                'akeneo.tailored_import.validation.required',
                '[mapping][true][0]',
                [
                    'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d6',
                    'type' => 'boolean_replacement',
                    'mapping' => [
                        'true' => [''],
                        'false' => ['No'],
                    ],
                ],
            ],
            'a boolean replacement with blank false in mapping' => [
                'akeneo.tailored_import.validation.required',
                '[mapping][false][0]',
                [
                    'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d6',
                    'type' => 'boolean_replacement',
                    'mapping' => [
                        'true' => ['Yes'],
                        'false' => [''],
                    ],
                ],
            ],
            'a boolean replacement with empty true in mapping' => [
                'akeneo.tailored_import.validation.required',
                '[mapping][true]',
                [
                    'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d6',
                    'type' => 'boolean_replacement',
                    'mapping' => [
                        'true' => [],
                        'false' => ['No'],
                    ],
                ],
            ],
            'a boolean replacement with empty false in mapping' => [
                'akeneo.tailored_import.validation.required',
                '[mapping][false]',
                [
                    'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d6',
                    'type' => 'boolean_replacement',
                    'mapping' => [
                        'true' => ['Yes'],
                        'false' => [],
                    ],
                ],
            ],
            'a boolean replacement with too long true in mapping' => [
                'akeneo.tailored_import.validation.max_length_reached',
                '[mapping][true][0]',
                [
                    'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d6',
                    'type' => 'boolean_replacement',
                    'mapping' => [
                        'true' => ['eeessvttvnttvtvvenntteeenteentsvsevstnseevntvnnstvsevnetneeenevnvstvntevvsvteeseseesteteveeenteevvvtvettensnenveetessnntnveesnnveseessevvntennettenvvvnsesttnnseneeneessnsesesseeeetteeevtesentntsvettveesvssvsesetseettvstsvensveetssnsesetvstettvevnessevsvsee'],
                        'false' => ['No'],
                    ],
                ],
            ],
            'a boolean replacement with too long false in mapping' => [
                'akeneo.tailored_import.validation.max_length_reached',
                '[mapping][false][0]',
                [
                    'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d6',
                    'type' => 'boolean_replacement',
                    'mapping' => [
                        'true' => ['Yes'],
                        'false' => ['eeessvttvnttvtvvenntteeenteentsvsevstnseevntvnnstvsevnetneeenevnvstvntevvsvteeseseesteteveeenteevvvtvettensnenveetessnntnveesnnveseessevvntennettenvvvnsesttnnseneeneessnsesesseeeetteeevtesentntsvettveesvssvsesetseettvstsvensveetssnsesetvstettvevnessevsvsee'],
                    ],
                ],
            ],
            'a boolean replacement with duplicate values in mapping' => [
                'akeneo.tailored_import.validation.source_values_should_be_unique',
                '[mapping][false]',
                [
                    'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d6',
                    'type' => 'boolean_replacement',
                    'mapping' => [
                        'true' => ['Yes'],
                        'false' => ['Yes'],
                    ],
                ],
            ],
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
