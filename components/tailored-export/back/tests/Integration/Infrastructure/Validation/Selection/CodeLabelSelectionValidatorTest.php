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

namespace Akeneo\Platform\TailoredExport\Test\Integration\Infrastructure\Validation\Selection;

use Akeneo\Platform\TailoredExport\Infrastructure\Validation\Selection\CodeLabelSelectionConstraint;
use Akeneo\Platform\TailoredExport\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

class CodeLabelSelectionValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validSelection
     */
    public function test_it_does_not_build_violations_on_valid_selection(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new CodeLabelSelectionConstraint());

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidSelection
     */
    public function test_it_builds_violations_on_invalid_selection(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        array $value
    ): void {
        $violations = $this->getValidator()->validate($value, new CodeLabelSelectionConstraint());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validSelection(): array
    {
        return [
            'a code selection' => [
                [
                    'type' => 'code',
                ],
            ],
            'a label selection' => [
                [
                    'type' => 'label',
                    'locale' => 'en_US',
                ],
            ],
        ];
    }

    public function invalidSelection(): array
    {
        return [
            'invalid type' => [
                'The value you selected is not a valid choice.',
                '[type]',
                [
                    'type' => 'invalid type',
                ],
            ],
            'blank locale' => [
                'This value should not be blank.',
                '[locale]',
                [
                    'type' => 'label',
                    'locale' => '',
                ],
            ],
            'inactive locale' => [
                'akeneo.tailored_export.validation.locale.should_be_active',
                '[locale]',
                [
                    'type' => 'label',
                    'locale' => 'fr_FR',
                ],
            ],
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
