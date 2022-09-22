<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation\DataMapping\Operation;

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Operation\ChangeCaseOperation;
use Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

final class ChangeCaseOperationValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validOperation
     */
    public function test_it_does_not_build_violations_when_operation_is_valid(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new ChangeCaseOperation());

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidOperation
     */
    public function test_it_build_violations_when_operation_is_invalid(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        array $value
    ): void {
        $violations = $this->getValidator()->validate($value, new ChangeCaseOperation());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validOperation(): array
    {
        return [
            'a valid change case operation' => [
                [
                    'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d6',
                    'type' => 'change_case',
                    'mode' => 'uppercase',
                ],
            ],
        ];
    }

    public function invalidOperation(): array
    {
        return [
            'a change case with wrong uuid' => [
                'This is not a valid UUID.',
                '[uuid]',
                [
                    'uuid' => 'invalid',
                    'type' => 'change_case',
                    'mode' => 'uppercase',
                ],
            ],
            'a change case with wrong type' => [
                'This value should be equal to {{ compared_value }}.',
                '[type]',
                [
                    'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d6',
                    'type' => 'invalid_operation',
                    'mode' => 'uppercase',
                ],
            ],
            'a change case with wrong mode' => [
                'The value you selected is not a valid choice.',
                '[mode]',
                [
                    'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d6',
                    'type' => 'change_case',
                    'mode' => 'invalid',
                ],
            ],
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
