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

use Akeneo\Platform\TailoredExport\Infrastructure\Validation\Operation\CleanHTMLTagsOperationConstraint;
use Akeneo\Platform\TailoredExport\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

class CleanHTMLTagsOperationValidatorTest extends AbstractValidationTest
{
    public function test_it_does_not_build_violations_on_valid_operation(): void
    {
        $violations = $this->getValidator()->validate([
            'type' => 'clean_html_tags',
            'value' => true,
        ], new CleanHTMLTagsOperationConstraint());

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
        $violations = $this->getValidator()->validate($value, new CleanHTMLTagsOperationConstraint());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function invalidOperation(): array
    {
        return [
            'invalid type' => [
                'This value should be equal to {{ compared_value }}.',
                '[type]',
                [
                    'type' => 'invalid type',
                    'value' => true,
                ],
            ],
            'invalid value' => [
                'This value should be of type {{ type }}.',
                '[value]',
                [
                    'type' => 'clean_html_tags',
                    'value' => 'azerty',
                ],
            ],
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
