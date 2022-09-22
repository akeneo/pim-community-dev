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

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Operation\CleanHTMLOperation;
use Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

final class CleanHTMLOperationValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validOperation
     */
    public function test_it_does_not_build_violations_when_operation_is_valid(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new CleanHTMLOperation());

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
        $violations = $this->getValidator()->validate($value, new CleanHTMLOperation());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validOperation(): array
    {
        return [
            'a valid clean html operation' => [
                [
                    'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d6',
                    'modes' => ['remove'],
                    'type' => 'clean_html',
                ],
            ],
        ];
    }

    public function invalidOperation(): array
    {
        return [
            'an invalid clean html' => [
                'This value should be equal to {{ compared_value }}.',
                '[type]',
                [
                    'type' => 'invalid_operation',
                ],
            ],
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
