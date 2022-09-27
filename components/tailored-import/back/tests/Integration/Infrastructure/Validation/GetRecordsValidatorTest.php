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
    public function test_it_validate_request_when_valid(): void
    {
        $request = new Request(
            request: [
                'channel' => 'ecommerce',
                'locale' => 'en_Us',
                'include_codes' => null,
                'exclude_codes' => null,
                'search' => null,
                'limit' => 25,
                'page' => 1
            ],
            attributes: [
                'reference_entity_code' => 'brand'
            ]
        );
        $violations = $this->getValidator()->validate($request, new GetRecords());

        $this->assertNoViolation($violations);
    }

    public function test_it_build_violation_if_no_locale(): void
    {
        $request = new Request(
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
        );
        $violations = $this->getValidator()->validate($request, new GetRecords());

        $this->assertHasValidationError('This value should not be null.', '[locale]',$violations);
    }

    public function test_it_build_violation_if_no_channel(): void
    {
        $request = new Request(
            request: [
                'channel' => null,
                'locale' => 'en_Us',
                'include_codes' => null,
                'exclude_codes' => null,
                'search' => null,
                'limit' => 25,
                'page' => 1
            ],
            attributes: [
                'reference_entity_code' => 'brand'
            ]
        );
        $violations = $this->getValidator()->validate($request, new GetRecords());

        $this->assertHasValidationError('This value should not be null.', '[channel]',$violations);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
