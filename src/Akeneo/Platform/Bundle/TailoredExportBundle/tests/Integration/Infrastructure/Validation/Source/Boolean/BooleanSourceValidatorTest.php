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

namespace Akeneo\Platform\TailoredExport\Test\Integration\Infrastructure\Validation\Source\Boolean;

use Akeneo\Platform\TailoredExport\Infrastructure\Validation\Source\Boolean\BooleanSourceConstraint;
use Akeneo\Platform\TailoredExport\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

class BooleanSourceValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validSource
     */
    public function test_it_does_not_build_violations_on_valid_source(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new BooleanSourceConstraint());

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidSource
     */
    public function test_it_builds_violations_on_invalid_source(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        array $value
    ): void {
        $violations = $this->getValidator()->validate($value, new BooleanSourceConstraint());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validSource(): array
    {
        return [
            'a valid boolean code selection' => [
                [
                    'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                    'code' => 'attribute_code',
                    'type' => 'attribute',
                    'channel' => null,
                    'locale' => null,
                    'selection' => [
                        'type' => 'code',
                    ],
                    'operations' => [],
                ],
            ],
            'a valid boolean selection with default value' => [
                [
                    'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                    'code' => 'attribute_code',
                    'type' => 'attribute',
                    'channel' => null,
                    'locale' => null,
                    'selection' => [
                        'type' => 'code',
                    ],
                    'operations' => [
                        'default_value' => [
                            'type' => 'default_value',
                            'value' => 'N/A',
                        ],
                    ],
                ],
            ],
            'a valid boolean selection with replacement' => [
                [
                    'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                    'code' => 'attribute_code',
                    'type' => 'attribute',
                    'channel' => null,
                    'locale' => null,
                    'selection' => [
                        'type' => 'code',
                    ],
                    'operations' => [
                        'replacement' => [
                            'type' => 'replacement',
                            'mapping' => [
                                'true' => 'vrai',
                                'false' => 'faux',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function invalidSource(): array
    {
        return [
            'an invalid selection type' => [
                'This value should be equal to "code".',
                '[selection][type]',
                [
                    'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                    'code' => 'attribute_code',
                    'type' => 'attribute',
                    'channel' => null,
                    'locale' => null,
                    'selection' => [
                        'type' => 'invalid_type',
                    ],
                    'operations' => [],
                ],
            ],
            'an invalid operation' => [
                'This field was not expected.',
                '[operations][invalid_operation]',
                [
                    'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                    'code' => 'attribute_code',
                    'type' => 'attribute',
                    'channel' => null,
                    'locale' => null,
                    'selection' => [
                        'type' => 'code',
                    ],
                    'operations' => [
                        'invalid_operation' => [
                            'type' => 'default_value',
                            'value' => 'N/A',
                        ],
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
