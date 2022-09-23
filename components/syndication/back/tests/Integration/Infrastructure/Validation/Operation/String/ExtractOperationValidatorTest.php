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

namespace Akeneo\Platform\Syndication\Test\Integration\Infrastructure\Validation\Operation\String;

use Akeneo\Platform\Syndication\Infrastructure\Validation\Operation\String\ExtractOperationConstraint;
use Akeneo\Platform\Syndication\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

class ExtractOperationValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validOperation
     */
    public function test_it_does_not_build_violations_on_valid_operation(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new ExtractOperationConstraint());

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
        $violations = $this->getValidator()->validate($value, new ExtractOperationConstraint());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validOperation(): array
    {
        return [
            'an extract operation' => [
                [
                    'type' => 'extract',
                    'regexp' => '/([a-z])/',
                ],
            ],
        ];
    }

    public function invalidOperation(): array
    {
        return [
            'invalid type' => [
                'This value should be equal to "extract".',
                '[type]',
                [
                    'type' => 'invalid type',
                    'value' => '/([a-z])/',
                ],
            ],
            'too long value' => [
                'akeneo.syndication.validation.max_length_reached',
                '[value]',
                [
                    'type' => 'extract',
                    'value' => str_repeat('m', 256),
                ],
            ],
            'invalid value type' => [
                'This value should be of type string.',
                '[value]',
                [
                    'type' => 'extract',
                    'value' => 123,
                ],
            ],
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
