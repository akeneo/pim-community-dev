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

namespace Akeneo\Platform\TailoredExport\Test\Integration\Infrastructure\Validation\Format;

use Akeneo\Platform\TailoredExport\Infrastructure\Validation\Format\Format;
use Akeneo\Platform\TailoredExport\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

class FormatValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validFormat
     */
    public function test_it_does_not_build_violations_on_valid_format(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new Format());

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidFormat
     */
    public function test_it_builds_violations_on_invalid_format(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        array $value
    ): void {
        $violations = $this->getValidator()->validate($value, new Format());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validFormat(): array
    {
        return [
            'format with text element' => [
                [
                    'type' => 'concat',
                    'space_between' => true,
                    'elements' => [
                        [
                            'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                            'type' => 'text',
                            'value' => 'a_value',
                        ],
                    ],
                ],
            ],
            'format with source element' => [
                [
                    'type' => 'concat',
                    'space_between' => false,
                    'elements' => [
                        [
                            'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                            'type' => 'text',
                            'value' => 'a_value',
                        ],
                    ],
                ],
            ],
            'format with lot of concatenation' => [
                [
                    'type' => 'concat',
                    'space_between' => false,
                    'elements' => array_merge(
                        array_fill(0, 10, [
                            'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                            'type' => 'text',
                            'value' => 'a_value',
                        ]),
                        array_fill(0, 3, [
                            'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                            'type' => 'source',
                            'value' => 'a_value',
                        ]),
                    ),
                ],
            ],
        ];
    }

    public function invalidFormat(): array
    {
        return [
            'invalid element type' => [
                'The value you selected is not a valid choice.',
                '[elements][51120b12-a2bc-41bf-aa53-cd73daf330d0][type]',
                [
                    'type' => 'concat',
                    'space_between' => true,
                    'elements' => [
                        [
                            'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                            'type' => 'invalid_type',
                            'value' => 'a_value',
                        ],
                    ],
                ],
            ],
            'max text element reached' => [
                'akeneo.tailored_export.validation.concatenation.max_text_count_reached',
                '[elements]',
                [
                    'type' => 'concat',
                    'space_between' => false,
                    'elements' => array_fill(0, 11, [
                        'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                        'type' => 'text',
                        'value' => 'a_value',
                    ]),
                ],
            ],
            'invalid format type' => [
                'The value you selected is not a valid choice.',
                '[type]',
                [
                    'type' => 'invalid type',
                    'space_between' => false,
                    'elements' => [
                        [
                            'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                            'type' => 'text',
                            'value' => 'a_value',
                        ],
                    ],
                ],
            ],
            'too long text value' => [
                'This value is too long. It should have {{ limit }} character or less.|This value is too long. It should have {{ limit }} characters or less.',
                '[elements][51120b12-a2bc-41bf-aa53-cd73daf330d0][value]',
                [
                    'type' => 'concat',
                    'space_between' => false,
                    'elements' => [
                        [
                            'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                            'type' => 'text',
                            'value' => str_repeat('m', 256),
                        ]
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
