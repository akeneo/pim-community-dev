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

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\SampleData;
use Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

class SampleDataValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validSampleData
     */
    public function test_it_does_not_build_violations_when_sample_data_are_valid(
        array $value
    ): void {
        $violations = $this->getValidator()->validate($value, new SampleData());

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidSampleData
     */
    public function test_it_build_violations_when_sample_data_are_invalid(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        array $value
    ): void {
        $violations = $this->getValidator()->validate($value, new SampleData());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validSampleData(): array
    {
        return [
            'a valid sample data' => [
                [
                    'value1',
                    'value2',
                    'value3',
                ]
            ],
            'a sample data with max character, null and int' => [
                [
                    str_repeat('a', 101),
                    '1',
                    null,
                ],
            ]
        ];
    }

    public function invalidSampleData(): array
    {
        return [
            'too much sample data' => [
                'This collection should contain 3 elements or less.',
                '',
                [
                    'value1',
                    'value2',
                    'value3',
                    'value4',
                ],
            ],
            'too long sample data' => [
                'This value is too long. It should have 101 characters or less.',
                '[0]',
                [
                    str_repeat('a', 102),
                    '1',
                    null,
                ]
            ],
            'an integer sample data' => [
                'This value should be of type string.',
                '[1]',
                [
                    'value1',
                    1,
                    null,
                ],
            ],
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
