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

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\GetRecords;
use Akeneo\Test\Integration\Configuration;
use Symfony\Component\HttpFoundation\Request;

final class GetRecordsValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validRequest
     */
    public function test_it_does_not_build_violations_when_request_is_valid(Request $value): void
    {
        $violations = $this->getValidator()->validate($value, new GetRecords());

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
        $violations = $this->getValidator()->validate($value, new GetRecords());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validRequest(): array
    {
        return [
            'valid request with all parameters' => [
                new Request(
                    request: [
                        'channel' => 'ecommerce',
                        'locale' => 'en_US',
                        'include_codes' => ['1'],
                        'exclude_codes' => ['1'],
                        'search' => null,
                        'limit' => 25,
                        'page' => 1
                    ],
                    attributes: [
                        'reference_entity_code' => 'brand'
                    ],
                ),
            ],
            'valid request without include & exclude codes' => [
                new Request(
                    request: [
                        'channel' => 'ecommerce',
                        'locale' => 'en_US',
                        'include_codes' => null,
                        'exclude_codes' => null,
                        'search' => null,
                        'limit' => 25,
                        'page' => 1
                    ],
                    attributes: [
                        'reference_entity_code' => 'brand'
                    ],
                ),
            ],
        ];
    }

    public function invalidRequest(): array
    {
        return [
            'request with invalid locale' => [
                'This value should not be null.',
                '[locale]',
                new Request(
                    request: [
                        'channel' => 'ecommerce',
                        'locale' => null,
                        'include_codes' => null,
                        'exclude_codes' => null,
                        'search' => null,
                        'limit' => 25,
                        'page' => 1
                    ],
                    attributes: [
                        'reference_entity_code' => 'brand'
                    ]
                ),
            ],
            'request with invalid channel' => [
                'This value should not be null.',
                '[channel]',
                new Request(
                    request: [
                        'channel' => null,
                        'locale' => 'en_US',
                        'include_codes' => null,
                        'exclude_codes' => null,
                        'search' => null,
                        'limit' => 25,
                        'page' => 1
                    ],
                    attributes: [
                        'reference_entity_code' => 'brand'
                    ]
                ),
            ],
            'request with invalid include_codes' => [
                'This value should be of type {{ type }}.',
                '[include_codes]',
                new Request(
                    request: [
                        'channel' => null,
                        'locale' => 'en_US',
                        'include_codes' => '1, 2, 3',
                        'exclude_codes' => null,
                        'search' => null,
                        'limit' => 25,
                        'page' => 1
                    ],
                    attributes: [
                        'reference_entity_code' => 'brand'
                    ]
                ),
            ],
            'request with invalid exclude_codes' => [
                'This value should be of type {{ type }}.',
                '[exclude_codes]',
                new Request(
                    request: [
                        'channel' => null,
                        'locale' => 'en_US',
                        'include_codes' => null,
                        'exclude_codes' => '1, 2, 3',
                        'search' => null,
                        'limit' => 25,
                        'page' => 1
                    ],
                    attributes: [
                        'reference_entity_code' => 'brand'
                    ]
                ),
            ],
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
