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

namespace Akeneo\Platform\TailoredExport\Test\Integration\Infrastructure\Validation\Source\QualityScore;

use Akeneo\Platform\TailoredExport\Infrastructure\Validation\Source\QualityScore\QualityScoreSourceConstraint;
use Akeneo\Platform\TailoredExport\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

class QualityScoreSourceValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validSource
     */
    public function test_it_does_not_build_violations_on_valid_source(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new QualityScoreSourceConstraint());

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
        $violations = $this->getValidator()->validate($value, new QualityScoreSourceConstraint());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validSource(): array
    {
        return [
            'a valid quality score code selection' => [
                [
                    'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                    'code' => 'quality_score',
                    'type' => 'property',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
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
                    'code' => 'quality_score',
                    'type' => 'property',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                    'selection' => [
                        'type' => 'invalid_type',
                    ],
                    'operations' => [],
                ],
            ],
            'a null channel' => [
                'This value should not be blank.',
                '[channel]',
                [
                    'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                    'code' => 'quality_score',
                    'type' => 'property',
                    'channel' => null,
                    'locale' => 'en_US',
                    'selection' => [
                        'type' => 'code',
                    ],
                    'operations' => [],
                ],
            ],
            'a null locale' => [
                'This value should not be blank.',
                '[locale]',
                [
                    'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                    'code' => 'quality_score',
                    'type' => 'property',
                    'channel' => 'ecommerce',
                    'locale' => null,
                    'selection' => [
                        'type' => 'code',
                    ],
                    'operations' => [],
                ],
            ],
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
