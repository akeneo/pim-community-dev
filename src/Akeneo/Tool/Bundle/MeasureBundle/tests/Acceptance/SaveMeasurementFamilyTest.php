<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\tests\Acceptance;

use Akeneo\Tool\Bundle\MeasureBundle\Application\SaveMeasurementFamily\SaveMeasurementFamilyCommand;
use Akeneo\Tool\Bundle\MeasureBundle\Model\LabelCollection;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Operation;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Unit;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SaveMeasurementFamilyTest extends AcceptanceTestCase
{
    /** * @var ValidatorInterface */
    private $validator;

    /** @var MeasurementFamilyRepositoryInterface */
    private $measurementFamilyRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->validator = $this->get('validator');
        $this->measurementFamilyRepository = $this->get('akeneo_measure.persistence.measurement_family_repository');
    }

    /**
     * @test
     * @dataProvider invalidCodes
     */
    public function it_does_not_create_measurement_family_because_the_code_is_invalid($invalidCode, string $errorMessage): void
    {
        $saveFamilyCommand = new SaveMeasurementFamilyCommand();
        $saveFamilyCommand->code = $invalidCode;
        $saveFamilyCommand->standardUnitCode = 'kilogram';
        $saveFamilyCommand->units = [['code' => 'kilogram']];

        $violations = $this->validator->validate($saveFamilyCommand);

        self::assertEquals(1, $violations->count());
        $violation = $violations->get(0);
        self::assertEquals($errorMessage, $violation->getMessage());
    }

    /**
     * @test
     * @dataProvider invalidLabels
     */
    public function it_does_not_create_measurement_family_because_the_standard_unit_is_invalid($invalidLabels, string $errorMessage): void
    {
        $saveFamilyCommand = new SaveMeasurementFamilyCommand();
        $saveFamilyCommand->code = 'WEIGHT';
        $saveFamilyCommand->labels = $invalidLabels;
        $saveFamilyCommand->standardUnitCode = 'kilogram';
        $saveFamilyCommand->units = [['code' => 'kilogram']];

        $violations = $this->validator->validate($saveFamilyCommand);

        self::assertEquals(1, $violations->count());
        $violation = $violations->get(0);
        self::assertEquals($errorMessage, $violation->getMessage());
    }

    /**
     * @test
     */
    public function it_does_not_create_measurement_family_because_the_standard_unit_is_not_a_unit_of_the_measurement_family(): void
    {
        $saveFamilyCommand = new SaveMeasurementFamilyCommand();
        $saveFamilyCommand->code = 'WEIGHT';
        $saveFamilyCommand->labels = [];
        $saveFamilyCommand->standardUnitCode = 'invalid_standard_unit_code';
        $saveFamilyCommand->units = [['code' => 'kilogram']];

        $violations = $this->validator->validate($saveFamilyCommand);

        self::assertEquals(1, $violations->count());
        $violation = $violations->get(0);
        self::assertEquals('The standard unit code "invalid_standard_unit_code" does not exist in the list of units for the measurement family.', $violation->getMessage());
    }

    public function invalidCodes(): array
    {
        return [
            'Not be too long' => [str_repeat('a', 256), 'This value is too long. It should have 255 characters or less.'],
            'Not blank' => [null, 'This value should not be blank.'],
            'Not a string' => [123, 'This value should be of type string.'],
            'unsupported character' => ['--nice-', 'This field may only contain letters, numbers and underscores.']
        ];
    }

    public function invalidLabels(): array
    {
        return [
            'Locale code should be a string' => [[123 => 'my label'], 'This value should be of type string.'],
            'Label should be a string' => [['fr_FR' => 12], 'This value should be of type string.']
        ];
    }
}
