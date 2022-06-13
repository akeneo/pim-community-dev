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

namespace Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation\DataMapping\Operation;

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Operation\SplitOperation;
use Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

final class SplitOperationValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validOperation
     */
    public function test_it_does_not_build_violations_when_operation_is_valid(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new SplitOperation());

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
        $violations = $this->getValidator()->validate($value, new SplitOperation());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validOperation(): array
    {
        return [
            'a valid split operation' => [
                [
                    'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d6',
                    'type' => 'split',
                    'separator' => ';',
                ],
            ],
        ];
    }

    public function invalidOperation(): array
    {
        return [
            'an invalid split' => [
                'This value should be equal to "split".',
                '[type]',
                [
                    'type' => 'invalid_operation',
                    'separator' => ';',
                ],
            ],
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
