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

namespace Akeneo\Platform\Syndication\Test\Integration\Infrastructure\Validation\Source\Measurement;

use Akeneo\Platform\Syndication\Infrastructure\Validation\Source\Measurement\MeasurementSourceConstraint;
use Akeneo\Platform\Syndication\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

class MeasurementSourceValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validSource
     */
    public function test_it_does_not_build_violations_on_valid_source(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new MeasurementSourceConstraint());

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
        $violations = $this->getValidator()->validate($value, new MeasurementSourceConstraint());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validSource(): array
    {
        return [
            'a valid measurement unit code selection' => [
                [
                    'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                    'code' => 'a_metric',
                    'type' => 'attribute',
                    'channel' => null,
                    'locale' => null,
                    'selection' => [
                        'type' => 'unit_code',
                    ],
                    'operations' => [],
                ],
            ],
            'a valid measurement unit label selection' => [
                [
                    'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                    'code' => 'a_metric',
                    'type' => 'attribute',
                    'channel' => null,
                    'locale' => null,
                    'selection' => [
                        'type' => 'unit_label',
                        'locale' => 'en_US',
                    ],
                    'operations' => [],
                ],
            ],
            'a valid measurement unit symbol selection' => [
                [
                    'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                    'code' => 'a_metric',
                    'type' => 'attribute',
                    'channel' => null,
                    'locale' => null,
                    'selection' => [
                        'type' => 'unit_symbol',
                    ],
                    'operations' => [],
                ],
            ],
            'a valid measurement value selection' => [
                [
                    'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                    'code' => 'a_metric',
                    'type' => 'attribute',
                    'channel' => null,
                    'locale' => null,
                    'selection' => [
                        'type' => 'value',
                    ],
                    'operations' => [],
                ],
            ],
            'a valid measurement value and unit label selection' => [
                [
                    'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                    'code' => 'a_metric',
                    'type' => 'attribute',
                    'channel' => null,
                    'locale' => null,
                    'selection' => [
                        'type' => 'value_and_unit_label',
                        'locale' => 'en_US',
                    ],
                    'operations' => [],
                ],
            ],
            'a valid measurement selection' => [
                [
                    'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                    'code' => 'a_metric',
                    'type' => 'attribute',
                    'channel' => null,
                    'locale' => null,
                    'selection' => [
                        'type' => 'measurement'
                    ],
                    'operations' => [],
                ],
            ],
            'a valid measurement value and unit symbol selection' => [
                [
                    'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                    'code' => 'a_metric',
                    'type' => 'attribute',
                    'channel' => null,
                    'locale' => null,
                    'selection' => [
                        'type' => 'value_and_unit_symbol',
                    ],
                    'operations' => [],
                ],
            ],
            'a valid measurement value selection with a valid decimal separator' => [
                [
                    'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                    'code' => 'a_metric',
                    'type' => 'attribute',
                    'channel' => null,
                    'locale' => null,
                    'selection' => [
                        'type' => 'value',
                        'decimal_separator' => ',',
                    ],
                    'operations' => [],
                ],
            ],
            'a valid measurement selection with default value' => [
                [
                    'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                    'code' => 'a_metric',
                    'type' => 'attribute',
                    'channel' => null,
                    'locale' => null,
                    'selection' => [
                        'type' => 'value',
                    ],
                    'operations' => [
                        'default_value' => [
                            'type' => 'default_value',
                            'value' => 'N/A',
                        ],
                    ],
                ],
            ],
            'a valid measurement selection with a conversion' => [
                [
                    'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                    'code' => 'a_metric',
                    'type' => 'attribute',
                    'channel' => null,
                    'locale' => null,
                    'selection' => [
                        'type' => 'value',
                    ],
                    'operations' => [
                        'measurement_conversion' => [
                            'type' => 'measurement_conversion',
                            'target_unit_code' => 'MEGAWATT',
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
                'The value you selected is not a valid choice.',
                '[selection][type]',
                [
                    'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                    'code' => 'a_metric',
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
                    'code' => 'a_metric',
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
            'a conversion to an unknown unit code' => [
                'akeneo.syndication.validation.measurement.unit.does_not_exist',
                '[operations][measurement_conversion][target_unit_code]',
                [
                    'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                    'code' => 'a_metric',
                    'type' => 'attribute',
                    'channel' => null,
                    'locale' => null,
                    'selection' => [
                        'type' => 'code',
                    ],
                    'operations' => [
                        'measurement_conversion' => [
                            'type' => 'measurement_conversion',
                            'target_unit_code' => 'unknown_unit_code',
                        ],
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
