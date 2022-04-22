<?php

namespace Akeneo\Platform\JobAutomation\Test\Acceptance\Infrastructure\Validation;

use Akeneo\Platform\JobAutomation\Infrastructure\Validation\Storage;

class ValidateStorageTest extends AbstractValidationTest
{
    /**
     * @dataProvider validStorages
     */
    public function testItDoesNotBuildViolationsWhenStoragesAreValid(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new Storage());

        $this->assertNoViolation($violations);
    }

    public function validStorages(): array
    {
        return [
            'valid storages' => [
                [
                    'type' => 'none',
                ],
            ],
        ];
    }
}
