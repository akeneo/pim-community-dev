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

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\DataMappingUuid;
use Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

class DataMappingUuidValidatorTest extends AbstractValidationTest
{
    public function test_it_does_not_build_violations_when_data_mapping_uuid_is_valid(): void
    {
        $violations = $this->getValidator()->validate('018e1a5e-4d77-4a15-add8-f142111d4cd0', new DataMappingUuid());

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidSampleData
     */
    public function test_it_build_violations_when_data_mapping_uuid_is_invalid(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        ?string $value
    ): void {
        $violations = $this->getValidator()->validate($value, new DataMappingUuid());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function invalidSampleData(): array
    {
        return [
            'not an uuid' => [
                'This is not a valid UUID.',
                '',
                'invalid_uuid',
            ],
            'null uuid' => [
                'This value should not be blank.',
                '',
                null
            ],
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
