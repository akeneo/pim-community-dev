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

namespace Akeneo\Platform\Syndication\Test\Integration\Infrastructure\Validation;

use Akeneo\Platform\Syndication\Infrastructure\Validation\IsValidAttribute;
use Akeneo\Test\Integration\Configuration;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
final class IsValidAttributeValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validAttribute
     */
    public function test_it_does_not_build_violations_when_attribute_is_valid(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new IsValidAttribute());

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidAttribute
     */
    public function test_it_build_violations_when_attribute_is_invalid(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        array $value
    ): void {
        $violations = $this->getValidator()->validate($value, new IsValidAttribute());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validAttribute(): array
    {
        return [
            'a non scopable and non localizable attribute' => [
                [
                    'code' => 'sku',
                    'channel' => null,
                    'locale' => null,
                ],
            ],
            'a scopable attribute' => [
                [
                    'code' => 'a_scopable_image',
                    'channel' => 'ecommerce',
                    'locale' => null,
                ],
            ],
            'a localizable attribute' => [
                [

                    'code' => 'a_localizable_image',
                    'channel' => null,
                    'locale' => 'en_US',
                ],
            ],
            'a localizable and scopable attribute' => [
                [
                    'code' => 'a_localized_and_scopable_text_area',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ],
            ],
        ];
    }

    public function invalidAttribute(): array
    {
        return [
            'give a locale to a non scopable and non localizable attribute' => [
                'akeneo.syndication.validation.attribute.locale_should_be_blank',
                '[locale]',
                [
                    'code' => 'sku',
                    'channel' => null,
                    'locale' => 'en_US',
                ],
            ],
            'give a channel to a non scopable and non localizable attribute' => [
                'akeneo.syndication.validation.attribute.channel_should_be_blank',
                '[channel]',
                [
                    'code' => 'sku',
                    'channel' => 'ecommerce',
                    'locale' => null,
                ],
            ],
            'a scopable attribute without channel' => [
                'akeneo.syndication.validation.attribute.channel_should_not_be_blank',
                '',
                [
                    'code' => 'a_scopable_image',
                    'channel' => null,
                    'locale' => null,
                ],
            ],
            'a scopable attribute with an inactive channel' => [
                'akeneo.syndication.validation.channel.should_exist',
                '[channel]',
                [
                    'code' => 'a_scopable_image',
                    'channel' => 'mobile',
                    'locale' => null,
                ],
            ],
            'a localizable attribute without locale' => [
                'akeneo.syndication.validation.attribute.locale_should_not_be_blank',
                '',
                [
                    'code' => 'a_localizable_image',
                    'channel' => null,
                    'locale' => null,
                ],
            ],
            'a localizable attribute with an inactive locale' => [
                'akeneo.syndication.validation.locale.should_be_active',
                '[locale]',
                [
                    'code' => 'a_localizable_image',
                    'channel' => null,
                    'locale' => 'br_FR',
                ],
            ],
            'a scopable and localizable attribute with an inactive locale ' => [
                'akeneo.syndication.validation.locale.should_be_bound_to_channel',
                '[locale]',
                [
                    'code' => 'a_localized_and_scopable_text_area',
                    'channel' => 'ecommerce',
                    'locale' => 'fr_FR',
                ],
            ],
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
