<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\tests\Acceptance;

use Akeneo\Test\Acceptance\Attribute\InMemoryIsThereAtLeastOneAttributeConfiguredWithMeasurementFamilyStub;
use Akeneo\Test\Acceptance\EventDispatcher\EventDispatcherMock;
use Akeneo\Test\Acceptance\MeasurementFamily\InMemoryMeasurementFamilyRepository;
use Akeneo\Tool\Bundle\MeasureBundle\Application\SaveMeasurementFamily\SaveMeasurementFamilyCommand;
use Akeneo\Tool\Bundle\MeasureBundle\Application\SaveMeasurementFamily\SaveMeasurementFamilyHandler;
use Akeneo\Tool\Bundle\MeasureBundle\Event\MeasurementFamilyUpdated;
use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Model\LabelCollection;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Operation;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Unit;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SaveMeasurementFamilyTest extends AcceptanceTestCase
{
    public EventDispatcherMock $eventDispatcherMock;
    private ValidatorInterface $validator;
    private InMemoryMeasurementFamilyRepository $measurementFamilyRepository;
    private SaveMeasurementFamilyHandler $saveMeasurementFamilyHandler;
    private InMemoryIsThereAtLeastOneAttributeConfiguredWithMeasurementFamilyStub $isThereAtLeastOneAttributeConfiguredWithMeasurementFamily;

    public function setUp(): void
    {
        parent::setUp();
        $this->validator = $this->get('validator');
        $this->measurementFamilyRepository = $this->get('akeneo_measure.persistence.measurement_family_repository');
        $this->measurementFamilyRepository->clear();
        $this->saveMeasurementFamilyHandler = $this->get('akeneo_measure.application.save_measurement_family_handler');
        $this->isThereAtLeastOneAttributeConfiguredWithMeasurementFamily = $this->get('akeneo.pim.structure.query.is_there_at_least_one_attribute_configured_with_measurement_family');
        $this->eventDispatcherMock = $this->get('event_dispatcher');
    }

    /**
     * @test
     */
    public function it_can_update_an_existing_measurement_family(): void
    {
        $measurementFamilyCode = 'weight';
        $standardUnitCode = 'KILOGRAM';
        $this->createMeasurementFamilyWithUnitsAndStandardUnit(
            $measurementFamilyCode,
            [$standardUnitCode],
            $standardUnitCode
        );

        // ton
        $updatedMeasurementFamilyLabel = ['fr_FR' => 'Another LABEL'];
        $tonUnitCode = 'ton';
        $tonLabels = ['fr_FR' => 'Tonne', 'en_US' => 'Ton'];
        $tonConversionOperator = ['operator' => 'mul', 'value' => '1'];
        $tonSymbol = 'T';
        //command
        $saveFamilyCommand = new SaveMeasurementFamilyCommand();
        $saveFamilyCommand->code = $measurementFamilyCode;
        $saveFamilyCommand->labels = $updatedMeasurementFamilyLabel;
        $saveFamilyCommand->standardUnitCode = $standardUnitCode;
        $saveFamilyCommand->units = [
            [
                'code' => $standardUnitCode,
                'labels' => [],
                'convert_from_standard' => [['operator' => 'mul', 'value' => '1']],
                'symbol' => 'kg'
            ],
            [
                'code' => $tonUnitCode,
                'labels' => $tonLabels,
                'convert_from_standard' => [$tonConversionOperator],
                'symbol' => $tonSymbol
            ],
        ];

        $violations = $this->validator->validate($saveFamilyCommand);
        $this->saveMeasurementFamilyHandler->handle($saveFamilyCommand);

        self::assertEquals(0, $violations->count());
        $measurementFamily = MeasurementFamily::create(
            MeasurementFamilyCode::fromString($measurementFamilyCode),
            LabelCollection::fromArray($updatedMeasurementFamilyLabel),
            UnitCode::fromString($standardUnitCode),
            [
                Unit::create(
                    UnitCode::fromString($standardUnitCode),
                    LabelCollection::fromArray([]),
                    [Operation::create("mul", "1")],
                    "kg"
                ),
                Unit::create(
                    UnitCode::fromString($tonUnitCode),
                    LabelCollection::fromArray($tonLabels),
                    [Operation::create($tonConversionOperator['operator'], $tonConversionOperator['value'])],
                    $tonSymbol
                ),
            ]
        );
        $actualMeasurementFamily = $this->measurementFamilyRepository->getByCode(
            MeasurementFamilyCode::fromString($measurementFamilyCode)
        );
        $this->assertEquals($measurementFamily, $actualMeasurementFamily);

        $events = $this->eventDispatcherMock->getEvents();
        $this->assertCount(1, $events);
        $event = current($events)['event'];
        $this->assertInstanceOf(MeasurementFamilyUpdated::class, $event);
        $this->assertEquals($measurementFamilyCode, $event->getMeasurementFamilyCode()->normalize());
    }

    /**
     * @test
     */
    public function it_cannot_create_if_the_standard_unit_code_operation_is_not_mul_1(): void
    {
        $saveFamilyCommand = new SaveMeasurementFamilyCommand();
        $saveFamilyCommand->code = 'WEIGHT';
        $saveFamilyCommand->labels = [];
        $saveFamilyCommand->standardUnitCode = 'KILOGRAM';
        $saveFamilyCommand->units = [
            [
                'code'                  => 'KILOGRAM',
                'labels'                => ['fr_FR' => 'Kilogrammes'],
                'convert_from_standard' => [['operator' => 'mul', 'value' => '0.1']],
                'symbol' => 'kg'
            ]
        ];
        $violations = $this->validator->validate($saveFamilyCommand);

        self::assertEquals(1, $violations->count());
        $violation = $violations->get(0);
        self::assertEquals('The standard unit code of the "WEIGHT" measurement family should be a multiply-by-1 operation', $violation->getMessage());
        self::assertEquals('units[0][convert_from_standard]', $violation->getPropertyPath());
    }

    /**
     * @test
     */
    public function it_cannot_change_the_standard_unit_code(): void
    {
        $measurementFamilyCode = 'WEIGHT';
        $this->createMeasurementFamilyWithUnitsAndStandardUnit($measurementFamilyCode, ['KILOGRAM', 'GRAM'], 'KILOGRAM');

        $saveFamilyCommand = new SaveMeasurementFamilyCommand();
        $saveFamilyCommand->code = $measurementFamilyCode;
        $saveFamilyCommand->labels = [];
        $saveFamilyCommand->standardUnitCode = 'GRAM';
        $saveFamilyCommand->units = [
            [
                'code'                  => 'KILOGRAM',
                'labels'                => ['fr_FR' => 'Kilogrammes'],
                'convert_from_standard' => [['operator' => 'mul', 'value' => '1']],
                'symbol' => 'km'
            ],
            [
                'code'                  => 'GRAM',
                'labels'                => ['fr_FR' => 'Gram'],
                'convert_from_standard' => [['operator' => 'mul', 'value' => '1']],
                'symbol' => 'km'
            ]
        ];
        $violations = $this->validator->validate($saveFamilyCommand);

        self::assertEquals(1, $violations->count());
        $violation = $violations->get(0);
        self::assertEquals('The standard unit code of the "WEIGHT" measurement family cannot be changed', $violation->getMessage());
        self::assertEquals('standard_unit_code', $violation->getPropertyPath());
    }

    /**
     * @test
     */
    public function it_adds_a_unit_even_if_the_measurement_family_is_linked_to_a_product_attribute(): void
    {
        $measurementFamilyCode = 'WEIGHT';
        $this->createMeasurementFamilyWithUnitsAndStandardUnit($measurementFamilyCode, ['KILOGRAM', 'GRAM'], 'KILOGRAM');
        $this->assertThereIsAProductAttributeLinkedToThisMeasurementFamily();
        $saveFamilyCommand = new SaveMeasurementFamilyCommand();
        $saveFamilyCommand->code = $measurementFamilyCode;
        $saveFamilyCommand->labels = [];
        $saveFamilyCommand->standardUnitCode = 'KILOGRAM';
        $saveFamilyCommand->units = [
            [
                'code'                  => 'KILOGRAM',
                'labels'                => ['fr_FR' => 'Kilogrammes'],
                'convert_from_standard' => [['operator' => 'mul', 'value' => '1']],
                'symbol' => 'km'
            ],
            [
                'code'                  => 'GRAM',
                'labels'                => [],
                'convert_from_standard' => [['operator' => 'mul', 'value' => '1']],
                'symbol' => 'km'
            ],
            [
                'code'                  => 'CENTIGRAM',
                'labels'                => [],
                'convert_from_standard' => [['operator' => 'mul', 'value' => '0.001']],
                'symbol' => 'cm'
            ]
        ];

        $violations = $this->validator->validate($saveFamilyCommand);

        self::assertEquals(0, $violations->count());
    }

    /**
     * @test
     * @dataProvider invalidCodes
     */
    public function it_has_an_invalid_code($invalidCode, string $expectedErrorMessage): void
    {
        $saveFamilyCommand = new SaveMeasurementFamilyCommand();
        $saveFamilyCommand->code = $invalidCode;
        $saveFamilyCommand->standardUnitCode = 'kilogram';
        $saveFamilyCommand->units = [
            [
                'code' => 'kilogram',
                'labels' => [],
                'convert_from_standard' => [['operator' => 'mul', 'value' => '153']],
                'symbol' => 'Km'
            ]
        ];

        $violations = $this->validator->validate($saveFamilyCommand);

        self::assertEquals(1, $violations->count());
        $violation = $violations->get(0);
        self::assertEquals($expectedErrorMessage, $violation->getMessage());
        self::assertEquals('code', $violation->getPropertyPath());
    }

    /**
     * @test
     * @dataProvider invalidLabels
     */
    public function it_has_an_invalid_label($invalidLabels, string $expectedErrorMessage, string $propertyPath): void
    {
        $saveFamilyCommand = new SaveMeasurementFamilyCommand();
        $saveFamilyCommand->code = 'WEIGHT';
        $saveFamilyCommand->labels = $invalidLabels;
        $saveFamilyCommand->standardUnitCode = 'kilogram';
        $saveFamilyCommand->units = [
            [
                'code' => 'kilogram',
                'labels' => [],
                'convert_from_standard' => [['operator' => 'mul', 'value' => '1']],
                'symbol' => 'Km'
            ]
        ];

        $violations = $this->validator->validate($saveFamilyCommand);

        self::assertEquals(1, $violations->count());
        $violation = $violations->get(0);
        self::assertEquals($expectedErrorMessage, $violation->getMessage());
        self::assertEquals($propertyPath, $violation->getPropertyPath());
    }

    /**
     * @test
     */
    public function it_does_not_specify_a_standard_unit_code(): void
    {
        $saveFamilyCommand = new SaveMeasurementFamilyCommand();
        $saveFamilyCommand->code = 'WEIGHT';
        $saveFamilyCommand->labels = [];
        $saveFamilyCommand->units = [
            [
                'code' => 'kilogram',
                'labels' => [],
                'convert_from_standard' => [['operator' => 'mul', 'value' => '153']],
                'symbol' => 'Km'
            ]
        ];

        $violations = $this->validator->validate($saveFamilyCommand);

        self::assertEquals(1, $violations->count());
        $violation = $violations->get(0);
        self::assertEquals('The standard unit is required.', $violation->getMessage());
        self::assertEquals('standard_unit_code', $violation->getPropertyPath());
    }

    /**
     * @test
     */
    public function it_has_a_standard_unit_which_is_not_a_unit_of_the_measurement_family(): void
    {
        $saveFamilyCommand = new SaveMeasurementFamilyCommand();
        $saveFamilyCommand->code = 'WEIGHT';
        $saveFamilyCommand->labels = [];
        $saveFamilyCommand->standardUnitCode = 'invalid_standard_unit_code';
        $saveFamilyCommand->units = [
            [
                'code' => 'kilogram',
                'labels' => [],
                'convert_from_standard' => [['operator' => 'mul', 'value' => '153']],
                'symbol' => 'Km'
            ]
        ];

        $violations = $this->validator->validate($saveFamilyCommand);

        self::assertEquals(1, $violations->count());
        $violation = $violations->get(0);
        self::assertEquals(
            'The "invalid_standard_unit_code" standard unit code does not exist in the list of units for the "WEIGHT" measurement family.',
            $violation->getMessage()
        );
        self::assertEquals('standard_unit_code', $violation->getPropertyPath());
    }

    /**
     * @test
     * @dataProvider invalidCodes
     */
    public function it_has_a_unit_with_an_invalid_code($invalidCode, string $expectedErrorMessage): void
    {
        $expectedPropertyPath = 'units[0][code]';
        $saveFamilyCommand = new SaveMeasurementFamilyCommand();
        $saveFamilyCommand->code = 'WEIGHT';
        $saveFamilyCommand->labels = [];
        $saveFamilyCommand->standardUnitCode = $invalidCode;
        $saveFamilyCommand->units = [
            [
                'code' => $invalidCode,
                'labels' => [],
                'convert_from_standard' => [['operator' => 'mul', 'value' => '153']],
                'symbol' => 'Km'
            ]
        ];

        $violations = $this->validator->validate($saveFamilyCommand);
        $actualViolation = null;
        foreach ($violations as $violation) {
            if ($violation->getMessage() === $expectedErrorMessage) {
                $actualViolation = $violation;
            }
        }
        self::assertNotNull(
            $actualViolation,
            sprintf('Expected to have a violation with message "%s" at path "%s"', $expectedErrorMessage, $expectedPropertyPath)
        );
        self::assertEquals($expectedErrorMessage, $violation->getMessage());
        self::assertEquals($expectedPropertyPath, $violation->getPropertyPath());
    }

    /**
     * @test
     * @dataProvider invalidUnitLabels
     */
    public function it_has_a_unit_with_an_invalid_label($invalidLabels, string $expectedErrorMessage, string $propertyPath): void
    {
        $saveFamilyCommand = new SaveMeasurementFamilyCommand();
        $saveFamilyCommand->code = 'WEIGHT';
        $saveFamilyCommand->labels = [];
        $saveFamilyCommand->standardUnitCode = 'kilogram';
        $saveFamilyCommand->units = [
            [
                'code' => 'kilogram',
                'labels' => $invalidLabels,
                'convert_from_standard' => [['operator' => 'mul', 'value' => '153']],
                'symbol' => 'Km'
            ]
        ];

        $violations = $this->validator->validate($saveFamilyCommand);

        self::assertEquals(1, $violations->count());
        $violation = $violations->get(0);
        self::assertEquals($expectedErrorMessage, $violation->getMessage());
        self::assertEquals($propertyPath, $violation->getPropertyPath());
    }

    /**
     * @test
     * @dataProvider invalidOperator
     */
    public function it_has_a_unit_with_an_invalid_convert_operator($invalidOperator, string $expectedErrorMessage): void
    {
        $saveFamilyCommand = new SaveMeasurementFamilyCommand();
        $saveFamilyCommand->code = 'WEIGHT';
        $saveFamilyCommand->labels = [];
        $saveFamilyCommand->standardUnitCode = 'kilogram';
        $saveFamilyCommand->units = [
            [
                'code' => 'kilogram',
                'labels' => [],
                'convert_from_standard' => [['operator' => $invalidOperator, 'value' => '251']],
                'symbol' => 'Km'
            ]
        ];

        $violations = $this->validator->validate($saveFamilyCommand);

        self::assertEquals(1, $violations->count());
        $violation = $violations->get(0);
        self::assertEquals($expectedErrorMessage, $violation->getMessage());
        self::assertEquals('units[0][convert_from_standard][0][operator]', $violation->getPropertyPath());
    }

    /**
     * @test
     * @dataProvider invalidConvertValue
     */
    public function it_has_a_unit_with_an_invalid_convert_value($invalidConvertValue, string $expectedErrorMessage): void
    {
        $saveFamilyCommand = new SaveMeasurementFamilyCommand();
        $saveFamilyCommand->code = 'WEIGHT';
        $saveFamilyCommand->labels = [];
        $saveFamilyCommand->standardUnitCode = 'kilogram';
        $saveFamilyCommand->units = [
            [
                'code' => 'kilogram',
                'labels' => [],
                'convert_from_standard' => [['operator' => 'mul', 'value' => $invalidConvertValue]],
                'symbol' => 'Km'
            ]
        ];

        $violations = $this->validator->validate($saveFamilyCommand);

        self::assertEquals(1, $violations->count());
        $violation = $violations->get(0);
        self::assertEquals($expectedErrorMessage, $violation->getMessage());
        self::assertEquals('units[0][convert_from_standard][0][value]', $violation->getPropertyPath());
    }

    /**
     * @test
     * @dataProvider invalidUnitSymbol
     */
    public function it_has_a_unit_with_an_invalid_unit_symbol($invalidUnitSymbol, string $expectedErrorMessage): void
    {
        $saveFamilyCommand = new SaveMeasurementFamilyCommand();
        $saveFamilyCommand->code = 'WEIGHT';
        $saveFamilyCommand->labels = [];
        $saveFamilyCommand->standardUnitCode = 'kilogram';
        $saveFamilyCommand->units = [
            [
                'code' => 'kilogram',
                'labels' => [],
                'convert_from_standard' => [['operator' => 'mul', 'value' => '255']],
                'symbol' => $invalidUnitSymbol
            ]
        ];

        $violations = $this->validator->validate($saveFamilyCommand);

        self::assertEquals(1, $violations->count());
        $violation = $violations->get(0);
        self::assertEquals($expectedErrorMessage, $violation->getMessage());
        self::assertEquals('units[0][symbol]', $violation->getPropertyPath());
    }

    /**
     * @test
     * @dataProvider invalidOperationCount
     */
    public function it_has_an_invalid_amount_of_operations($invalidOperationCount, string $expectedErrorMessage): void
    {
        $saveFamilyCommand = new SaveMeasurementFamilyCommand();
        $saveFamilyCommand->code = 'WEIGHT';
        $saveFamilyCommand->labels = [];
        $saveFamilyCommand->standardUnitCode = 'kilogram';
        $saveFamilyCommand->units = [
            [
                'code' => 'kilogram',
                'labels' => [],
                'convert_from_standard' => array_fill(0, $invalidOperationCount, ['operator' => 'mul', 'value' => '1']),
                'symbol' => 'Kg',
            ]
        ];

        $violations = $this->validator->validate($saveFamilyCommand);

        self::assertEquals(1, $violations->count());
        $violation = $violations->get(0);
        self::assertEquals($expectedErrorMessage, $violation->getMessage());
        self::assertEquals('units[0][convert_from_standard]', $violation->getPropertyPath());
    }

    /**
     * @test
     * @dataProvider invalidUnitCount
     */
    public function it_has_an_invalid_amount_of_units($numberOfUnits, string $expectedErrorMessage): void
    {
        $expectedPropertyPath = 'units';
        $saveFamilyCommand = new SaveMeasurementFamilyCommand();
        $saveFamilyCommand->code = 'WEIGHT';
        $saveFamilyCommand->labels = [];
        $saveFamilyCommand->standardUnitCode = 0 === $numberOfUnits ? '' : 'unit_0';
        $saveFamilyCommand->units = 0 === $numberOfUnits ? [] : array_map(static fn (int $i) => [
            'code' => sprintf('unit_%d', $i),
            'labels' => [],
            'convert_from_standard' => [['operator' => 'mul', 'value' => '1']],
            'symbol' => 'Kg',
        ], range(0, $numberOfUnits - 1));

        $violations = $this->validator->validate($saveFamilyCommand);

        $actualViolation = null;
        foreach ($violations as $violation) {
            if ($violation->getMessage() === $expectedErrorMessage) {
                $actualViolation = $violation;
            }
        }
        self::assertNotNull(
            $actualViolation,
            sprintf('Expected to have a violation with message "%s" at path "%s"', $expectedErrorMessage, $expectedPropertyPath)
        );
        self::assertEquals($expectedErrorMessage, $violation->getMessage());
        self::assertEquals($expectedPropertyPath, $violation->getPropertyPath());
    }

    /**
     * @test
     */
    public function it_does_not_allow_to_remove_a_unit_when_linked_to_a_product_attribute(): void
    {
        $measurementFamilyCode = 'WEIGHT';
        $this->createMeasurementFamilyWithUnitsAndStandardUnit($measurementFamilyCode, ['KILOGRAM', 'GRAM'], 'KILOGRAM');
        $this->assertThereIsAProductAttributeLinkedToThisMeasurementFamily();

        $saveFamilyCommand = new SaveMeasurementFamilyCommand();
        $saveFamilyCommand->code = $measurementFamilyCode;
        $saveFamilyCommand->labels = [];
        $saveFamilyCommand->standardUnitCode = 'KILOGRAM';
        $saveFamilyCommand->units = [
            [
                'code'                  => 'KILOGRAM',
                'labels'                => [],
                'convert_from_standard' => [['operator' => 'mul', 'value' => '1']],
                'symbol' => 'km'
            ],
            // Missing GRAM
        ];
        $violations = $this->validator->validate($saveFamilyCommand);

        self::assertEquals(1, $violations->count());
        $violation = $violations->get(0);
        self::assertEquals('A product attribute is linked to this measurement family. You can only edit the translated labels and symbol of a unit.', $violation->getMessage());
        self::assertEquals('units', $violation->getPropertyPath());
    }

    /**
     * @test
     * @dataProvider invalidConvertionOperations
     */
    public function it_does_not_allow_to_update_the_conversion_of_a_unit_when_linked_to_a_product_attribute(array $invalidOperations): void
    {
        $measurementFamilyCode = 'WEIGHT';
        $this->measurementFamilyRepository->save(
            MeasurementFamily::create(
                MeasurementFamilyCode::fromString($measurementFamilyCode),
                LabelCollection::fromArray([]),
                UnitCode::fromString('KILOGRAM'),
                [
                    Unit::create(
                        UnitCode::fromString('KILOGRAM'),
                        LabelCollection::fromArray([]),
                        [Operation::create("mul", "1")],
                        "km",
                    ),
                    Unit::create(
                        UnitCode::fromString('GRAM'),
                        LabelCollection::fromArray([]),
                        [Operation::create("mul", "0.000001")],
                        "km",
                    )
                ]
            )
        );
        $this->assertThereIsAProductAttributeLinkedToThisMeasurementFamily();

        $saveFamilyCommand = new SaveMeasurementFamilyCommand();
        $saveFamilyCommand->code = $measurementFamilyCode;
        $saveFamilyCommand->labels = [];
        $saveFamilyCommand->standardUnitCode = 'KILOGRAM';
        $saveFamilyCommand->units = [
            [
                'code'                  => 'KILOGRAM',
                'labels'                => [],
                'convert_from_standard' => [['operator' => 'mul', 'value' => '1']],
                'symbol' => 'km'
            ],
            [
                'code'                  => 'GRAM',
                'labels'                => [],
                'convert_from_standard' => $invalidOperations,
                'symbol' => 'km'
            ],
        ];
        $violations = $this->validator->validate($saveFamilyCommand);

        self::assertEquals(1, $violations->count());
        $violation = $violations->get(0);
        self::assertEquals('A product attribute is linked to this measurement family. You can only edit the translated labels and symbol of a unit', $violation->getMessage());
        self::assertEquals('', $violation->getPropertyPath());
    }

    /**
     * @test
     */
    public function it_cannot_have_the_same_unit_code_twice(): void
    {
        $saveFamilyCommand = new SaveMeasurementFamilyCommand();
        $saveFamilyCommand->code = 'WEIGHT';
        $saveFamilyCommand->labels = [];
        $saveFamilyCommand->standardUnitCode = 'KILOGRAM';
        $saveFamilyCommand->units = [
            [
                'code'                  => 'KILOGRAM',
                'labels'                => ['fr_FR' => 'Kilogramme'],
                'convert_from_standard' => [['operator' => 'mul', 'value' => '1']],
                'symbol' => 'km'
            ],
            [
                'code'                  => 'KILOGRAM',
                'labels'                => ['fr_FR' => 'New Kilogramme'],
                'convert_from_standard' => [['operator' => 'mul', 'value' => '0.000001']],
                'symbol' => 'km'
            ],
        ];
        $violations = $this->validator->validate($saveFamilyCommand);

        self::assertEquals(1, $violations->count());
        $violation = $violations->get(0);
        self::assertEquals('We found some duplicated units in the measurement family. The measurement family requires unique units.', $violation->getMessage());
        self::assertEquals('units', $violation->getPropertyPath());
    }

    public function invalidCodes(): array
    {
        return [
            'Should not be too long' => [
                str_repeat('a', 256),
                'This value is too long. It should have 255 characters or less.'
            ],
            'Should not blank' => [null, 'This value should not be blank.'],
            'Should not a string' => [123, 'This value should be of type string.'],
            'Should not have unsupported character' => ['--nice-', 'This field can only contain letters, numbers, and underscores.']
        ];
    }

    public function invalidLabels(): array
    {
        $as = str_repeat('a', 101);

        return [
            'Locale code should be a string' => [[123 => 'my label'], 'This value should be of type string.', 'labels[123]'],
            'Locale code cannot be too long' => [[$as => 'my label'], 'This value is too long. It should have 100 characters or less.', sprintf('labels[%s]', $as)],
            'Label should be a string' => [['fr_FR' => 12], 'This value should be of type string.', 'labels[fr_FR]'],
            'Label cannot be too long' => [['fr_FR' => $as], 'This value is too long. It should have 100 characters or less.', 'labels[fr_FR]']
        ];
    }

    public function invalidUnitLabels(): array
    {
        $as = str_repeat('a', 101);

        return [
            'Locale code should be a string' => [[123 => 'my label'], 'This value should be of type string.', 'units[0][labels][123]'],
            'Locale code cannot be too long' => [[$as => 'my label'], 'This value is too long. It should have 100 characters or less.', sprintf('units[0][labels][%s]', $as)],
            'Label should be a string' => [['fr_FR' => 12], 'This value should be of type string.', 'units[0][labels][fr_FR]'],
            'Label cannot be too long' => [['fr_FR' => $as], 'This value is too long. It should have 100 characters or less.', 'units[0][labels][fr_FR]']
        ];
    }

    public function invalidOperator(): array
    {
        return [
            'Operator cannot be blank' => [null, 'This value should not be blank.'],
            'Operator is not supported' => ['invalid_operator', 'The "invalid_operator" operator is invalid, please use "mul", "div", "add", "sub" instead.'],
        ];
    }

    public function invalidConvertValue(): array
    {
        return [
            'The convert value is not a valid number represented as a string' => ['1.24adv', 'The operation value should be a valid number']
        ];
    }

    public function invalidUnitSymbol()
    {
        return [
            'Should not be too long' => [str_repeat('a', 256), 'This value is too long. It should have 255 characters or less.'],
            'Should be a string' => [123, 'This value should be of type string.'],
        ];
    }

    private function assertThereIsAProductAttributeLinkedToThisMeasurementFamily(): void
    {
        $this->isThereAtLeastOneAttributeConfiguredWithMeasurementFamily->setStub(true);
    }

    private function createMeasurementFamilyWithUnitsAndStandardUnit(string $measurementFamilyCode, array $unitCodes, string $standardUnitCode): void
    {
        $this->measurementFamilyRepository->save(
            MeasurementFamily::create(
                MeasurementFamilyCode::fromString($measurementFamilyCode),
                LabelCollection::fromArray([]),
                UnitCode::fromString($standardUnitCode),
                array_map(static fn (string $unitCode) => Unit::create(
                    UnitCode::fromString($unitCode),
                    LabelCollection::fromArray([]),
                    [
                        Operation::create("mul", "1"),
                    ],
                    "km",
                ), $unitCodes)
            )
        );
    }

    public function invalidOperationCount()
    {
        return [
            'Should have at least one operation' => [0, 'A minimum of one conversion operation per unit is required.'],
            'Should have max 5 operations' => [6, 'You’ve reached the limit of 5 conversion operations per unit.'],
        ];
    }

    public function invalidUnitCount()
    {
        return [
            'Should have at least one operation' => [0, 'There should be at least 1 unit in the measurement family.'],
            'Cannot have more than 50 operations' => [51, 'You’ve reached the limit of 50 conversion operations per unit.'],
        ];
    }

    public function invalidConvertionOperations()
    {
        return [
            'Cannot update an existing operation' => [[['operator' => 'mul', 'value' => '50']]],
            'Cannot add an operation' => [[['operator' => 'mul', 'value' => '0.000001'], ['operator' => 'add', 'value' => '50']]],
        ];
    }

    private function assertMeasurementFamilyDoesNotExists(string $measurementFamilyCode): void
    {
        try {
            $this->measurementFamilyRepository->getByCode(MeasurementFamilyCode::fromString($measurementFamilyCode));
        } catch (MeasurementFamilyNotFoundException $e) {
            return;
        }

        self::assertTrue(
            false,
            sprintf('Measurement family "%s" exists, expected not to exist', $measurementFamilyCode)
        );
    }
}
