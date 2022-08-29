<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Unit\Infrastructure\Validation;

use Akeneo\Catalogs\Application\Persistence\GetMeasurementsFamilyQueryInterface;
use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\AttributeCriterionContainsValidMeasurement;
use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\AttributeCriterionContainsValidMeasurementValidator;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeCriterionContainsValidMeasurementValidatorTest extends ConstraintValidatorTestCase
{
    private ?GetMeasurementsFamilyQueryInterface $getMeasurementsFamilyQuery;

    protected function setUp(): void
    {
        $this->getMeasurementsFamilyQuery = $this->createMock(GetMeasurementsFamilyQueryInterface::class);

        parent::setUp();
    }

    protected function createValidator(): AttributeCriterionContainsValidMeasurementValidator
    {
        return new AttributeCriterionContainsValidMeasurementValidator($this->getMeasurementsFamilyQuery);
    }

    public function testItValidates(): void
    {
        $this->getMeasurementsFamilyQuery
            ->method('execute')
            ->with('Weight', 'en_US')
            ->willReturn([
                'code' => 'Weight',
                'units' => [
                    ["code" => "MICROGRAM", "label" => "Microgram"],
                    ["code" => "MILLIGRAM", "label" => "Milligram"],
                ]
            ]);

        $payload = [
            'field' => 'Weight',
            'value' => [
                'unit' => 'MICROGRAM',
            ],
        ];

        $this->validator->validate($payload, new AttributeCriterionContainsValidMeasurement());

        $this->assertNoViolation();
    }

    public function testItDoesNotValidate(): void
    {
        $this->getMeasurementsFamilyQuery
            ->method('execute')
            ->with('Weight', 'en_US')
            ->willReturn([
                'code' => 'Weight',
                'units' => [
                    ["code" => "MICROGRAM", "label" => "Microgram"],
                    ["code" => "MILLIGRAM", "label" => "Milligram"],
                ]
            ]);

        $payload = [
            'field' => 'Weight',
            'value' => [
                'unit' => 'KILOGRAM',
            ],
        ];

        $this->validator->validate($payload, new AttributeCriterionContainsValidMeasurement());

        $this->buildViolation('akeneo_catalogs.validation.product_selection.criteria.measurement.unit.not_exist')
            ->setParameter('{{ field }}', 'Weight')
            ->atPath('property.path[locale]')
            ->assertRaised();
    }



    public function testItDoesNotValidateWhenMeasurementFamilyDoesNotExist(): void
    {
        $this->getMeasurementsFamilyQuery
            ->method('execute')
            ->with('Weight_with_typo', 'en_US')
            ->willReturn(null);

        $payload = [
            'field' => 'Weight_with_typo',
            'value' => [
                'unit' => 'KILOGRAM',
            ],
        ];

        $this->validator->validate($payload, new AttributeCriterionContainsValidMeasurement());

        $this->buildViolation('akeneo_catalogs.validation.product_selection.criteria.measurement.unit.measurement_family_unknown')
            ->setParameter('{{ field }}', 'Weight_with_typo')
            ->atPath('property.path[locale]')
            ->assertRaised();
    }

    public function testItValidatesWhenValueIsNull(): void
    {
        $payload = [
            'field' => 'Weight',
            'value' => null,
        ];

        $this->validator->validate($payload, new AttributeCriterionContainsValidMeasurement());

        $this->assertNoViolation();
    }

    public function testItThrowsAnExceptionIfInvalidConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $payload = [
            'field' => 'Weight',
            'value' => null,
        ];

        $this->validator->validate($payload, new NotBlank());
    }
}
