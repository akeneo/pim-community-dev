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

use Akeneo\Platform\TailoredExport\Infrastructure\Validation\Operation\MeasurementConversionOperationConstraint;
use Akeneo\Platform\TailoredExport\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

class MeasurementConversionOperationValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validOperation
     */
    public function test_it_does_not_build_violations_on_valid_operation(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new MeasurementConversionOperationConstraint(['attributeCode' => 'a_metric']));

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
        $violations = $this->getValidator()->validate($value, new MeasurementConversionOperationConstraint(['attributeCode' => 'a_metric']));

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validOperation(): array
    {
        return [
            'a measurement conversion' => [
                [
                    'type' => 'measurement_conversion',
                    'target_unit_code' => 'KILOWATT',
                ],
            ],
        ];
    }

    public function invalidOperation(): array
    {
        return [
            'invalid type' => [
                'This value should be equal to "measurement_conversion".',
                '[type]',
                [
                    'type' => 'invalid type',
                    'target_unit_code' => 'WATT',
                ],
            ],
            'invalid target_unit_code' => [
                'akeneo.tailored_export.validation.measurement.unit.does_not_exist',
                '[target_unit_code]',
                [
                    'type' => 'measurement_conversion',
                    'target_unit_code' => 'BAR',
                ],
            ],
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
