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

namespace Akeneo\Platform\TailoredExport\Test\Integration\Infrastructure\Validation;

use Akeneo\Platform\TailoredExport\Infrastructure\Validation\GetReferenceEntityAttributesQuery;
use Akeneo\Test\Integration\Configuration;
use Symfony\Component\HttpFoundation\Request;

final class GetReferenceEntityAttributesQueryValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validRequest
     */
    public function test_it_does_not_build_violations_when_request_is_valid(Request $value): void
    {
        $violations = $this->getValidator()->validate($value, new GetReferenceEntityAttributesQuery());

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
        $violations = $this->getValidator()->validate($value, new GetReferenceEntityAttributesQuery());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validRequest(): array
    {
        return [
            'valid request ' => [
                new Request([
                    'reference_entity_code' => 'designer',
                ]),
            ],
        ];
    }

    public function invalidRequest(): array
    {
        return [
            'invalid request with no Reference Entity code' => [
                'This value should not be blank.',
                '[reference_entity_code]',
                new Request([]),
            ],
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
