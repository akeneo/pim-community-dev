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

namespace Akeneo\Platform\Syndication\Test\Integration\Infrastructure\Validation\Source\Scalar;

use Akeneo\Platform\Syndication\Infrastructure\Validation\Source\Scalar\ScalarSourceConstraint;
use Akeneo\Platform\Syndication\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

class ScalarSourceValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validSource
     */
    public function test_it_does_not_build_violations_on_valid_source(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new ScalarSourceConstraint());

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
        $violations = $this->getValidator()->validate($value, new ScalarSourceConstraint());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validSource(): array
    {
        return [
            'a valid scalar selection' => [
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
