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

namespace Akeneo\Platform\Syndication\Test\Integration\Infrastructure\Validation\Source\SimpleAssociationType;

use Akeneo\Platform\Syndication\Infrastructure\Validation\Source\SimpleAssociationType\SimpleAssociationTypeSourceConstraint;
use Akeneo\Platform\Syndication\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

class SimpleAssociationTypeValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validSource
     */
    public function test_it_does_not_build_violations_on_valid_source(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new SimpleAssociationTypeSourceConstraint());

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
        $violations = $this->getValidator()->validate($value, new SimpleAssociationTypeSourceConstraint());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validSource(): array
    {
        return [
            'a valid simple association products code selection' => [
                [
                    'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                    'code' => 'attribute_code',
                    'type' => 'attribute',
                    'channel' => null,
                    'locale' => null,
                    'selection' => [
                        'entity_type' => 'products',
                        'type' => 'code',
                        'separator' => ',',
                    ],
                    'operations' => [],
                ],
            ],
            'a valid simple association product models code selection' => [
                [
                    'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                    'code' => 'attribute_code',
                    'type' => 'attribute',
                    'channel' => null,
                    'locale' => null,
                    'selection' => [
                        'entity_type' => 'product_models',
                        'type' => 'code',
                        'separator' => ',',
                    ],
                    'operations' => [],
                ],
            ],
            'a valid simple association groupss code selection' => [
                [
                    'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                    'code' => 'attribute_code',
                    'type' => 'attribute',
                    'channel' => null,
                    'locale' => null,
                    'selection' => [
                        'entity_type' => 'groups',
                        'type' => 'code',
                        'separator' => ',',
                    ],
                    'operations' => [],
                ],
            ],
            'a valid simple association products label selection' => [
                [
                    'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                    'code' => 'attribute_code',
                    'type' => 'attribute',
                    'channel' => null,
                    'locale' => null,
                    'selection' => [
                        'entity_type' => 'products',
                        'type' => 'label',
                        'separator' => ',',
                        'locale' => 'en_US',
                        'channel' => 'ecommerce',
                    ],
                    'operations' => [],
                ],
            ],
            'a valid simple association product models label selection' => [
                [
                    'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                    'code' => 'attribute_code',
                    'type' => 'attribute',
                    'channel' => null,
                    'locale' => null,
                    'selection' => [
                        'entity_type' => 'product_models',
                        'type' => 'label',
                        'separator' => ',',
                        'locale' => 'en_US',
                        'channel' => 'ecommerce',
                    ],
                    'operations' => [],
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
                    'code' => 'attribute_code',
                    'type' => 'attribute',
                    'channel' => null,
                    'locale' => null,
                    'selection' => [
                        'entity_type' => 'products',
                        'type' => 'invalid_type',
                        'separator' => ',',
                    ],
                    'operations' => [],
                ],
            ],
            'an invalid selection separator' => [
                'The value you selected is not a valid choice.',
                '[selection][separator]',
                [
                    'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                    'code' => 'attribute_code',
                    'type' => 'attribute',
                    'channel' => null,
                    'locale' => null,
                    'selection' => [
                        'entity_type' => 'products',
                        'type' => 'code',
                        'separator' => 'invalid_separator',
                    ],
                    'operations' => [],
                ],
            ],
            'an invalid selection entity type' => [
                'The value you selected is not a valid choice.',
                '[selection][entity_type]',
                [
                    'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                    'code' => 'attribute_code',
                    'type' => 'attribute',
                    'channel' => null,
                    'locale' => null,
                    'selection' => [
                        'entity_type' => 'invalid_entity_type',
                        'type' => 'code',
                        'separator' => ',',
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
                        'entity_type' => 'products',
                        'type' => 'code',
                        'separator' => ',',
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
