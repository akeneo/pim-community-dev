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

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\PropertyTarget;
use Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

class PropertyTargetValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validPropertyTarget
     */
    public function test_it_does_not_build_violations_when_property_target_is_valid(
        array $value
    ): void {
        $violations = $this->getValidator()->validate($value, new PropertyTarget());

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidPropertyTarget
     */
    public function test_it_build_violations_when_property_target_is_invalid(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        array  $value
    ): void {
        $violations = $this->getValidator()->validate($value, new PropertyTarget());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validPropertyTarget(): array
    {
        return [
            'a property target' => [
                [
                    'code' => 'categories',
                    'type' => 'property',
                    'action_if_not_empty' => 'set',
                    'action_if_empty' => 'skip'
                ],
            ],
            'a property target with add assignation' => [
                [
                    'code' => 'categories',
                    'type' => 'property',
                    'action_if_not_empty' => 'add',
                    'action_if_empty' => 'skip'
                ],
            ],
            'a property target with clear value' => [
                [
                    'code' => 'categories',
                    'type' => 'property',
                    'action_if_not_empty' => 'set',
                    'action_if_empty' => 'clear'
                ],
            ],
        ];
    }

    public function invalidPropertyTarget(): array
    {
        return [
            'a property target with wrong type' => [
                'This value should be equal to "property".',
                '[type]',
                [
                    'code' => 'categories',
                    'type' => 'attribute',
                ],
            ],
            'a property target with locale' => [
                'This field was not expected.',
                '[locale]',
                [
                    'code' => 'categories',
                    'locale' => 'fr_FR',
                    'type' => 'property',
                ],
            ],
            'a property target with channel' => [
                'This field was not expected.',
                '[channel]',
                [
                    'code' => 'categories',
                    'channel' => 'ecommerce',
                    'type' => 'property',
                ],
            ],
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
