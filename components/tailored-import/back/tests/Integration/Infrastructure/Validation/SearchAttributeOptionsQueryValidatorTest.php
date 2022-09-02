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

namespace Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation;

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\SearchAttributeOptionsQuery;
use Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;
use Symfony\Component\HttpFoundation\Request;

final class SearchAttributeOptionsQueryValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validRequest
     */
    public function test_it_does_not_build_violations_when_request_is_valid(Request $value): void
    {
        $violations = $this->getValidator()->validate($value, new SearchAttributeOptionsQuery());

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidRequest
     */
    public function test_it_builds_violations_when_request_is_invalid(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        Request $value,
    ): void {
        $violations = $this->getValidator()->validate($value, new SearchAttributeOptionsQuery());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validRequest(): array
    {
        return [
            'valid request with all parameters' => [
                new Request([], [
                    'include_codes' => ['1'],
                    'exclude_codes' => ['1'],
                    'search' => 'nice',
                    'locale' => 'en_US',
                    'page' => 1,
                    'limit' => 25,
                ]),
            ],
            'valid request without include & exclude codes' => [
                new Request([], [
                    'include_codes' => null,
                    'exclude_codes' => null,
                    'search' => 'nice',
                    'locale' => 'en_US',
                    'page' => 1,
                    'limit' => 25,
                ]),
            ],
        ];
    }

    public function invalidRequest(): array
    {
        return [
            'invalid request with missing attribute code' => [
                'This field is missing.',
                '[search]',
                new Request([], [
                    'include_codes' => ['1'],
                    'exclude_codes' => ['1'],
                    'locale' => 'en_US',
                    'page' => 1,
                    'limit' => 25,
                ]),
            ],
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
