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
                    'action' => 'set',
                    'ifEmpty' => 'skip',
                    'onError' => 'skipLine',
                ]
            ],
            'a valid attribute target' => [
                [
                    'code' => 'description',
                    'type' => 'attribute',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                    'action' => 'set',
                    'ifEmpty' => 'skip',
                    'onError' => 'skipLine',
                ]
            ],
            'a valid property target' => [
                [
                    'code' => 'category',
                    'type' => 'property',
                    'action' => 'add',
                    'ifEmpty' => 'skip',
                    'onError' => 'skipLine',
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
                    'action' => 'set',
                    'ifEmpty' => 'skip',
                    'onError' => 'skipLine',
                ],
            ],
            'a target with missing type' => [
                'This field is missing.',
                '[type]',
                [
                    'code' => 'description',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                    'action' => 'set',
                    'ifEmpty' => 'skip',
                    'onError' => 'skipLine',
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
                    'action' => 'set',
                    'ifEmpty' => 'skip',
                    'onError' => 'skipLine',
                ],
            ],
            'an attribute target with invalid code' => [
                'akeneo.tailored_import.validation.target.attribute_should_exists',
                '[code]',
                [
                    'code' => 'invalid_attribute',
                    'type' => 'attribute',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                    'action' => 'set',
                    'ifEmpty' => 'skip',
                    'onError' => 'skipLine',
                ],
            ],
            'a property target with invalid code' => [
                'akeneo.tailored_import.validation.target.property_should_exists',
                '[code]',
                [
                    'code' => 'invalid_property',
                    'type' => 'property',
                    'action' => 'set',
                    'ifEmpty' => 'skip',
                    'onError' => 'skipLine',
                ],
            ],
            'an attribute target without channel when attribute is scopable' => [
                'This field is missing.',
                '[channel]',
                [
                    'code' => 'description',
                    'type' => 'attribute',
                    'locale' => 'en_US',
                    'action' => 'set',
                    'ifEmpty' => 'skip',
                    'onError' => 'skipLine',
                ],
            ],
            'an attribute target without locale when attribute is localizable' => [
                'This field is missing.',
                '[locale]',
                [
                    'code' => 'description',
                    'type' => 'attribute',
                    'channel' => 'ecommerce',
                    'action' => 'set',
                    'ifEmpty' => 'skip',
                    'onError' => 'skipLine',
                ],
            ],
            'an attribute target with invalid channel' => [
                'akeneo.tailored_import.validation.channel.should_exist',
                '[channel]',
                [
                    'code' => 'description',
                    'type' => 'attribute',
                    'channel' => 'perlinpinpin',
                    'locale' => 'en_US',
                    'action' => 'set',
                    'ifEmpty' => 'skip',
                    'onError' => 'skipLine',
                ],
            ],
            'an attribute target with invalid locale' => [
                'akeneo.tailored_import.validation.locale.should_be_active',
                '[locale]',
                [
                    'code' => 'description',
                    'type' => 'attribute',
                    'channel' => 'ecommerce',
                    'locale' => 'ru_RU',
                    'action' => 'set',
                    'ifEmpty' => 'skip',
                ],
            ],
            'a target with missing action' => [
                'This field is missing.',
                '[action]',
                [
                    'code' => 'description',
                    'type' => 'attribute',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                    'ifEmpty' => 'skip',
                    'onError' => 'skipLine',
                ]
            ],
            'a target with invalid action' => [
                'The value you selected is not a valid choice.',
                '[action]',
                [
                    'code' => 'description',
                    'type' => 'attribute',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                    'action' => 'invalid',
                    'ifEmpty' => 'skip',
                    'onError' => 'skipLine',
                ]
            ],
            'a target with missing ifEmpty' => [
                'This field is missing.',
                '[ifEmpty]',
                [
                    'code' => 'description',
                    'type' => 'attribute',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                    'action' => 'set',
                    'onError' => 'skipLine',
                ]
            ],
            'a target with invalid ifEmpty' => [
                'The value you selected is not a valid choice.',
                '[ifEmpty]',
                [
                    'code' => 'description',
                    'type' => 'attribute',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                    'action' => 'set',
                    'ifEmpty' => 'invalid',
                    'onError' => 'skipLine',
                ]
            ],
            'a target with missing onError' => [
                'This field is missing.',
                '[onError]',
                [
                    'code' => 'description',
                    'type' => 'attribute',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                    'action' => 'set',
                    'ifEmpty' => 'skip',
                ]
            ],
            'a target with invalid onError' => [
                'The value you selected is not a valid choice.',
                '[onError]',
                [
                    'code' => 'description',
                    'type' => 'attribute',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                    'action' => 'set',
                    'ifEmpty' => 'skip',
                    'onError' => 'invalid',
                ]
            ],
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
