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

namespace Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation\DataMapping;

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\AttributeTarget;
use Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

class AttributeTargetValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validAttributeTarget
     */
    public function test_it_does_not_build_violations_when_attribute_target_is_valid(
        array $value
    ): void {
        $violations = $this->getValidator()->validate($value, new AttributeTarget());

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidAttributeTarget
     */
    public function test_it_build_violations_when_attribute_target_is_invalid(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        array  $value
    ): void {
        $violations = $this->getValidator()->validate($value, new AttributeTarget());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validAttributeTarget(): array
    {
        return [
            'a non scopable and non localizable attribute target' => [
                [
                    'code' => 'sku',
                    'channel' => null,
                    'locale' => null,
                    'type' => 'attribute',
                    'attribute_type' => 'pim_catalog_identifier',
                    'source_configuration' => [],
                    'action_if_not_empty' => null,
                    'action_if_empty' => null
                ],
            ],
            'a scopable attribute target' => [
                [
                    'code' => 'a_scopable_image',
                    'channel' => 'ecommerce',
                    'locale' => null,
                    'type' => 'attribute',
                    'attribute_type' => 'pim_catalog_image',
                    'source_configuration' => [],
                    'action_if_not_empty' => null,
                    'action_if_empty' => null
                ],
            ],
            'a localizable attribute target' => [
                [

                    'code' => 'a_localizable_image',
                    'channel' => null,
                    'locale' => 'en_US',
                    'type' => 'attribute',
                    'attribute_type' => 'pim_catalog_image',
                    'source_configuration' => [],
                    'action_if_not_empty' => null,
                    'action_if_empty' => null
                ],
            ],
            'a localizable and scopable attribute target' => [
                [
                    'code' => 'a_localized_and_scopable_text_area',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                    'type' => 'attribute',
                    'attribute_type' => 'pim_catalog_textarea',
                    'source_configuration' => [],
                    'action_if_not_empty' => null,
                    'action_if_empty' => null
                ],
            ],
        ];
    }

    public function invalidAttributeTarget(): array
    {
        return [
            'an property target' => [
                'This value should be equal to {{ compared_value }}.',
                '[type]',
                [
                    'code' => 'categories',
                    'channel' => null,
                    'locale' => null,
                    'type' => 'property',
                ],
            ],
            'an attribute target localizable without locale' => [
                'akeneo.tailored_import.validation.attribute.locale_should_not_be_blank',
                '[code]',
                [
                    'code' => 'a_localized_and_scopable_text_area',
                    'channel' => 'ecommerce',
                    'locale' => null,
                    'type' => 'attribute',
                    'attribute_type' => 'pim_catalog_textarea',
                ],
            ],
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
