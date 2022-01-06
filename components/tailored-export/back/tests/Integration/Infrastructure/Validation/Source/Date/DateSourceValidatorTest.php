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

namespace Akeneo\Platform\TailoredExport\Test\Integration\Infrastructure\Validation\Source\Date;

use Akeneo\Platform\TailoredExport\Infrastructure\Validation\Source\Date\DateSourceConstraint;
use Akeneo\Platform\TailoredExport\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

class DateSourceValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validSource
     */
    public function test_it_does_not_build_violations_on_valid_source(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new DateSourceConstraint());

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
        $violations = $this->getValidator()->validate($value, new DateSourceConstraint());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validSource(): array
    {
        return [
            'a valid date selection' => [
                [
                    'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                    'code' => 'attribute_code',
                    'type' => 'attribute',
                    'channel' => null,
                    'locale' => null,
                    'selection' => [
                        'format' => 'yyyy/mm/dd',
                    ],
                    'operations' => [],
                ],
            ],
            'a valid date selection with default value' => [
                [
                    'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                    'code' => 'attribute_code',
                    'type' => 'attribute',
                    'channel' => null,
                    'locale' => null,
                    'selection' => [
                        'format' => 'dd.mm.yy',
                    ],
                    'operations' => [
                        'default_value' => [
                            'type' => 'default_value',
                            'value' => 'N/A',
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
                'This field was not expected.',
                '[selection][type]',
                [
                    'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                    'code' => 'attribute_code',
                    'type' => 'attribute',
                    'channel' => null,
                    'locale' => null,
                    'selection' => [
                        'type' => 'code',
                    ],
                ],
            ],
            'an invalid date format' => [
                'The value you selected is not a valid choice.',
                '[selection][format]',
                [
                    'uuid' => '51120b12-a2bc-41bf-aa53-cd73daf330d0',
                    'code' => 'attribute_code',
                    'type' => 'attribute',
                    'channel' => null,
                    'locale' => null,
                    'selection' => [
                        'format' => 'invalid_format',
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
