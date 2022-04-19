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

namespace Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation\DataMapping;

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Sources;
use Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

class SourcesValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validSources
     */
    public function test_it_does_not_build_violations_when_data_mappings_are_valid(
        bool $supportsMultiSource,
        array $value
    ): void {
        $violations = $this->getValidator()->validate($value, new Sources($supportsMultiSource, [
            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc69',
            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc68',
            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc67',
            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc66',
            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc65',
        ]));

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidSources
     */
    public function test_it_build_violations_when_data_mappings_are_invalid(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        bool $supportsMultiSource,
        array $value
    ): void {
        $violations = $this->getValidator()->validate($value, new Sources($supportsMultiSource, [
            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc69',
            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc68',
            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc67',
            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc66',
            '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc65',
        ]));

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validSources(): array
    {
        return [
            'a valid sources on a mono source target' => [
                false,
                ['9cecaeaf-d4d0-40be-9b78-53d5a1a5fc69'],
            ],
            'a valid sources on a multi source target' => [
                true,
                [
                    '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc69',
                    '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc68',
                    '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc67',
                    '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc66',
                ],
            ],
            'a multi source target with one source' => [
                true,
                ['9cecaeaf-d4d0-40be-9b78-53d5a1a5fc69'],
            ],
        ];
    }

    public function invalidSources(): array
    {
        return [
            'empty source on a mono source target' => [
                'akeneo.tailored_import.validation.data_mappings.sources.count_mismatched',
                '',
                false,
                [],
            ],
            'empty source on a multi source target' => [
                'akeneo.tailored_import.validation.data_mappings.sources.at_least_one_required',
                '',
                true,
                [],
            ],
            'source referring to non existent column' => [
                'akeneo.tailored_import.validation.data_mappings.sources.should_exist',
                '[1]',
                true,
                [
                    '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc69',
                    '9cecaeaf-d4d0-40be-9b78-53d5a1a5fg04',
                ],
            ],
            'duplicated sources' => [
                'akeneo.tailored_import.validation.data_mappings.sources.should_be_unique',
                '',
                true,
                [
                    '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc69',
                    '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc68',
                    '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc69',
                ],
            ],
            'too much source on a mono source target' => [
                'akeneo.tailored_import.validation.data_mappings.sources.count_mismatched',
                '',
                false,
                [
                    '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc69',
                    '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc68',
                ],
            ],
            'too much source on a multi source target' => [
                'akeneo.tailored_import.validation.data_mappings.sources.max_count_reached',
                '',
                true,
                [
                    '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc69',
                    '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc68',
                    '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc67',
                    '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc66',
                    '9cecaeaf-d4d0-40be-9b78-53d5a1a5fc65',
                ],
            ],
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
