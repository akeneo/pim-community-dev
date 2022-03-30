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

namespace Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation;

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\Target;
use Akeneo\Test\Integration\Configuration;

final class TargetValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validTarget
     */
    public function test_it_does_not_build_violations_when_target_is_valid(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new Target());

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidTarget
     */
    public function test_it_build_violations_when_target_is_invalid(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        array $value
    ): void {
        $violations = $this->getValidator()->validate($value, new Target());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validTarget(): array
    {
        return [
            'a valid identifier attribute target' => [
                [
                    'code' => 'sku',
                    'type' => 'attribute',
                    'channel' => null,
                    'locale' => null,
                    'action_if_not_empty' => 'set',
                    'action_if_empty' => 'skip',
                ]
            ],
            'a valid attribute target' => [
                [
                    'code' => 'a_localized_and_scopable_text_area',
                    'type' => 'attribute',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                    'action_if_not_empty' => 'set',
                    'action_if_empty' => 'skip',
                ]
            ],
            'a valid property target' => [
                [
                    'code' => 'categories',
                    'type' => 'property',
                    'action_if_not_empty' => 'add',
                    'action_if_empty' => 'skip',
                ]
            ],
            'a valid measurement target' => [
                [
                    'code' => 'a_metric',
                    'type' => 'attribute',
                    'channel' => null,
                    'locale' => null,
                    'action_if_not_empty' => 'set',
                    'action_if_empty' => 'skip',
                    'source_parameter' => [
                        'unit' => 'WATT',
                    ]
                ]
            ],
        ];
    }

    public function invalidTarget(): array
    {
        return [
            'a target with missing code' => [
                'This field is missing.',
                '[code]',
                [
                    'type' => 'attribute',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                    'action_if_not_empty' => 'set',
                    'action_if_empty' => 'skip',
                ],
            ],
            'a target with missing type' => [
                'This field is missing.',
                '[type]',
                [
                    'code' => 'description',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                    'action_if_not_empty' => 'set',
                    'action_if_empty' => 'skip',
                ],
            ],
            'a target with invalid type' => [
                'The value you selected is not a valid choice.',
                '[type]',
                [
                    'code' => 'description',
                    'type' => 'invalid',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                    'action_if_not_empty' => 'set',
                    'action_if_empty' => 'skip',
                ],
            ],
            'an attribute target with invalid code' => [
                'akeneo.tailored_import.validation.target.attribute_should_exist',
                '[code]',
                [
                    'code' => 'invalid_attribute',
                    'type' => 'attribute',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                    'action_if_not_empty' => 'set',
                    'action_if_empty' => 'skip',
                ],
            ],
            'a property target with invalid code' => [
                'akeneo.tailored_import.validation.target.property_should_exist',
                '[code]',
                [
                    'code' => 'invalid_property',
                    'type' => 'property',
                    'action_if_not_empty' => 'set',
                    'action_if_empty' => 'skip',
                ],
            ],
            'an attribute target without channel when attribute is scopable' => [
                'This field is missing.',
                '[channel]',
                [
                    'code' => 'a_scopable_image',
                    'type' => 'attribute',
                    'locale' => 'en_US',
                    'action_if_not_empty' => 'set',
                    'action_if_empty' => 'skip',
                ],
            ],
            'an attribute target without locale when attribute is localizable' => [
                'This field is missing.',
                '[locale]',
                [
                    'code' => 'a_localizable_image',
                    'type' => 'attribute',
                    'channel' => 'ecommerce',
                    'action_if_not_empty' => 'set',
                    'action_if_empty' => 'skip',
                ],
            ],
            'an attribute target with invalid channel' => [
                'akeneo.tailored_import.validation.channel.should_exist',
                '[channel]',
                [
                    'code' => 'a_localized_and_scopable_text_area',
                    'type' => 'attribute',
                    'channel' => 'perlinpinpin',
                    'locale' => 'en_US',
                    'action_if_not_empty' => 'set',
                    'action_if_empty' => 'skip',
                ],
            ],
            'an attribute target with invalid locale' => [
                'akeneo.tailored_import.validation.locale.should_be_active',
                '[locale]',
                [
                    'code' => 'a_localized_and_scopable_text_area',
                    'type' => 'attribute',
                    'channel' => 'ecommerce',
                    'locale' => 'ru_RU',
                    'action_if_not_empty' => 'set',
                    'action_if_empty' => 'skip',
                ],
            ],
            'a target with missing action_if_not_empty' => [
                'This field is missing.',
                '[action_if_not_empty]',
                [
                    'code' => 'a_localized_and_scopable_text_area',
                    'type' => 'attribute',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                    'action_if_empty' => 'skip',
                ]
            ],
            'a target with invalid action_if_not_empty' => [
                'The value you selected is not a valid choice.',
                '[action_if_not_empty]',
                [
                    'code' => 'a_localized_and_scopable_text_area',
                    'type' => 'attribute',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                    'action_if_not_empty' => 'invalid',
                    'action_if_empty' => 'skip',
                ]
            ],
            'a target with missing action_if_empty' => [
                'This field is missing.',
                '[action_if_empty]',
                [
                    'code' => 'a_localized_and_scopable_text_area',
                    'type' => 'attribute',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                    'action_if_not_empty' => 'set',
                ]
            ],
            'a target with invalid action_if_empty' => [
                'The value you selected is not a valid choice.',
                '[action_if_empty]',
                [
                    'code' => 'a_localized_and_scopable_text_area',
                    'type' => 'attribute',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                    'action_if_not_empty' => 'set',
                    'action_if_empty' => 'invalid',
                ]
            ],
            'a target with invalid metric family unit in source_parameter' => [
                'akeneo.tailored_import.validation.target.source_parameter.unit_should_exist',
                '[source_parameter]',
                [
                    'code' => 'a_metric',
                    'type' => 'attribute',
                    'channel' => null,
                    'locale' => null,
                    'action_if_not_empty' => 'set',
                    'action_if_empty' => 'skip',
                    'source_parameter' => [
                        'unit' => 'FOO',
                    ]
                ]
            ],
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
