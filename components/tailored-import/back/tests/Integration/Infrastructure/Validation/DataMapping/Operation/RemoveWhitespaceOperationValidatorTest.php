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

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Operation\RemoveWhitespaceOperation;
use Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

final class RemoveWhitespaceOperationValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validOperation
     */
    public function test_it_does_not_build_violations_when_operation_is_valid(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new RemoveWhitespaceOperation());

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
        $violations = $this->getValidator()->validate($value, new RemoveWhitespaceOperation());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validOperation(): array
    {
        return [
            'a valid remove whitespace operation' => [
                [
                    'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d6',
                    'type' => 'remove_whitespace',
                    'modes' => ['consecutive', 'trim'],
                ],
            ],
        ];
    }

    public function invalidOperation(): array
    {
        return [
            'a remove whitespace operation with wrong uuid' => [
                'This is not a valid UUID.',
                '[uuid]',
                [
                    'uuid' => 'invalid',
                    'type' => 'remove_whitespace',
                    'modes' => ['trim'],
                ],
            ],
            'a remove whitespace with wrong type' => [
                'This value should be equal to {{ compared_value }}.',
                '[type]',
                [
                    'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d6',
                    'type' => 'invalid_operation',
                    'modes' => ['trim'],
                ],
            ],
            'a remove whitespace with wrong modes' => [
                'One or more of the given values is invalid.',
                '[modes]',
                [
                    'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d6',
                    'type' => 'remove_whitespace',
                    'modes' => ['invalid'],
                ],
            ],
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
