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

namespace Akeneo\Platform\Syndication\Test\Integration\Infrastructure\Validation\Operation;

use Akeneo\Platform\Syndication\Infrastructure\Validation\Operation\DefaultValueOperationConstraint;
use Akeneo\Platform\Syndication\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

class DefaultValueOperationValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validOperation
     */
    public function test_it_does_not_build_violations_on_valid_operation(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new DefaultValueOperationConstraint());

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
        $violations = $this->getValidator()->validate($value, new DefaultValueOperationConstraint());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validOperation(): array
    {
        return [
            'a default value' => [
                [
                    'type' => 'default_value',
                    'value' => 'foo',
                ],
            ],
        ];
    }

    public function invalidOperation(): array
    {
        return [
            'invalid type' => [
                'This value should be equal to "default_value".',
                '[type]',
                [
                    'type' => 'invalid type',
                    'value' => 'bar',
                ],
            ],
            'too long value' => [
                'akeneo.syndication.validation.max_length_reached',
                '[value]',
                [
                    'type' => 'default_value',
                    'value' => str_repeat('m', 256),
                ],
            ],
            'invalid value type' => [
                'This value should be of type string.',
                '[value]',
                [
                    'type' => 'default_value',
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
