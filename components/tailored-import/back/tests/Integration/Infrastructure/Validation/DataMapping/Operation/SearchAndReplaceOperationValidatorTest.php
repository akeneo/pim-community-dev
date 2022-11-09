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

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Operation\SearchAndReplaceOperation;
use Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

class SearchAndReplaceOperationValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validOperation
     */
    public function test_it_does_not_build_violations_on_valid_operation(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new SearchAndReplaceOperation());

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
        $violations = $this->getValidator()->validate($value, new SearchAndReplaceOperation());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validOperation(): array
    {
        return [
            'a valid search and replace operation' => [
                [
                    'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d6',
                    'type' => 'search_and_replace',
                    'replacements' => [
                        [
                            'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d7',
                            'what' => 'replace me',
                            'with' => 'with that',
                            'case_sensitive' => true,
                        ],
                        [
                            'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d8',
                            'what' => 'rEplaCe me',
                            'with' => 'WiTh thAt',
                            'case_sensitive' => false,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function invalidOperation(): array
    {
        return [
            'too long "with" value' => [
                'akeneo.tailored_import.validation.max_length_reached',
                '[replacements][ad4e2d5c-2830-4ba8-bf83-07f9935063d8][with]',
                [
                    'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d6',
                    'type' => 'search_and_replace',
                    'replacements' => [
                        [
                            'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d7',
                            'what' => 'replace me',
                            'with' => 'with that',
                            'case_sensitive' => true,
                        ],
                        [
                            'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d8',
                            'what' => 'rEplaCe me',
                            'with' => str_repeat('a', 256),
                            'case_sensitive' => false,
                        ],
                    ],
                ],
            ],
            'empty "what" value' => [
                'This value should not be blank.',
                '[replacements][ad4e2d5c-2830-4ba8-bf83-07f9935063d7][what]',
                [
                    'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d6',
                    'type' => 'search_and_replace',
                    'replacements' => [
                        [
                            'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d7',
                            'what' => '',
                            'with' => 'with that',
                            'case_sensitive' => true,
                        ],
                        [
                            'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d8',
                            'what' => 'rEplaCe me',
                            'with' => 'with that',
                            'case_sensitive' => false,
                        ],
                    ],
                ],
            ],
            'too many replacements' => [
                'akeneo.tailored_import.validation.operations.max_replacements_reached',
                '[replacements]',
                [
                    'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d6',
                    'type' => 'search_and_replace',
                    'replacements' => array_fill(0, 11, [
                        'uuid' => 'ad4e2d5c-2830-4ba8-bf83-07f9935063d7',
                        'what' => 'replace me',
                        'with' => 'with that',
                        'case_sensitive' => true,
                    ]),
                ],
            ],
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
