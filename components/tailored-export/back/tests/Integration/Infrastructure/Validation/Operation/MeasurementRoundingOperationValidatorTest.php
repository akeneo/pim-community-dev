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

namespace Akeneo\Platform\TailoredExport\Test\Integration\Infrastructure\Validation\Operation;

use Akeneo\Platform\TailoredExport\Infrastructure\Validation\Operation\MeasurementRoundingOperationConstraint;
use Akeneo\Platform\TailoredExport\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

class MeasurementRoundingOperationValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validOperation
     */
    public function test_it_does_not_build_violations_on_valid_operation(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new MeasurementRoundingOperationConstraint());

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidOperation
     */
    public function test_it_builds_violations_on_invalid_operation(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        array $value
    ): void {
        $violations = $this->getValidator()->validate(
            $value,
            new MeasurementRoundingOperationConstraint()
        );
        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validOperation(): array
    {
        return [
            'a measurement standard rounding' => [
                [
                    'type' => 'measurement_rounding',
                    'rounding_type' => 'standard',
                    'precision' => 2
                ],
            ],
            'a measurement round up' => [
                [
                    'type' => 'measurement_rounding',
                    'rounding_type' => 'round_up',
                    'precision' => 2,
                ],
            ],
            'a measurement round down' => [
                [
                    'type' => 'measurement_rounding',
                    'rounding_type' => 'round_down',
                    'precision' => 2,
                ],
            ],
        ];
    }

    public function invalidOperation(): array
    {
        return [
            'invalid type' => [
                'This value should be equal to {{ compared_value }}.',
                '[type]',
                [
                    'type' => 'invalid type',
                    'rounding_type' => 'standard',
                    'precision' => 2
                ],
            ],
            'invalid rounding_type' => [
                'The value you selected is not a valid choice.',
                '[rounding_type]',
                [
                    'type' => 'measurement_rounding',
                    'rounding_type' => 'invalid',
                    'precision' => 2
                ],
            ],
            'invalid rounding_type is blank' => [
                'This value should not be blank.',
                '[rounding_type]',
                [
                    'type' => 'measurement_rounding',
                    'rounding_type' => null,
                    'precision' => 2
                ],
            ],
            'invalid precision should not be blank' => [
                'akeneo.tailored_export.column_details.sources.operation.measurement_rounding.precision.validation.precision_should_not_be_blank',
                '[precision]',
                [
                    'type' => 'measurement_rounding',
                    'rounding_type' => 'standard',
                    'precision' => null
                ],
            ],
            'invalid precision should be integers' => [
                'This value should be of type {{ type }}.',
                '[precision]',
                [
                    'type' => 'measurement_rounding',
                    'rounding_type' => 'standard',
                    'precision' => 'a 5'
                ],
            ],
            'invalid precision should not be > 12' => [
                'akeneo.tailored_export.column_details.sources.operation.measurement_rounding.precision.validation.precision_is_out_of_range',
                '[precision]',
                [
                    'type' => 'measurement_rounding',
                    'rounding_type' => 'standard',
                    'precision' => 15
                ],
            ],
            'invalid precision should not be < 0' => [
                'akeneo.tailored_export.column_details.sources.operation.measurement_rounding.precision.validation.precision_is_out_of_range',
                '[precision]',
                [
                    'type' => 'measurement_rounding',
                    'rounding_type' => 'standard',
                    'precision' => -5
                ],
            ],
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
