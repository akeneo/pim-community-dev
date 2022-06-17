<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Unit\Infrastructure\Validation;

use Akeneo\Catalogs\Infrastructure\Validation\CriteriaJson;
use Akeneo\Catalogs\Infrastructure\Validation\CriteriaJsonValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CriteriaJsonValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator()
    {
        return new CriteriaJsonValidator();
    }

    public function testItValidates(): void
    {
        $value = [
            [
                'field' => 'status',
                'operator' => '=',
                'value' => false,
            ]
        ];

        $this->validator->validate($value, new CriteriaJson());

        $this->assertNoViolation();
    }

    public function testItDoesNotValidate(): void
    {
        $value = [
            [
                'field' => 'wrong-field',
                'operator' => '=',
                'value' => false,
            ]
        ];

        $this->validator->validate($value, new CriteriaJson());

        $this->buildViolation('Does not have a value in the enumeration ["status"]')
            ->atPath('property.path./0/field')
            ->buildNextViolation('Failed to match exactly one schema')
            ->atPath('property.path./0')
            ->assertRaised();
    }
}
