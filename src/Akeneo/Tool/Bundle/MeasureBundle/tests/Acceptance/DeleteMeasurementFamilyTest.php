<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\tests\Acceptance;

use Akeneo\Test\Acceptance\Attribute\InMemoryIsThereAtLeastOneAttributeConfiguredWithMeasurementFamilyStub;
use Akeneo\Test\Acceptance\EventDispatcher\EventDispatcherMock;
use Akeneo\Test\Acceptance\MeasurementFamily\InMemoryMeasurementFamilyRepository;
use Akeneo\Tool\Bundle\MeasureBundle\Application\DeleteMeasurementFamily\DeleteMeasurementFamilyCommand;
use Akeneo\Tool\Bundle\MeasureBundle\Application\DeleteMeasurementFamily\DeleteMeasurementFamilyHandler;
use Akeneo\Tool\Bundle\MeasureBundle\Event\MeasurementFamilyDeleted;
use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Model\LabelCollection;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Operation;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Unit;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DeleteMeasurementFamilyTest extends AcceptanceTestCase
{
    public EventDispatcherMock $eventDispatcherMock;
    private ValidatorInterface $validator;
    private InMemoryMeasurementFamilyRepository $measurementFamilyRepository;
    private DeleteMeasurementFamilyHandler $deleteMeasurementFamilyHandler;
    private InMemoryIsThereAtLeastOneAttributeConfiguredWithMeasurementFamilyStub $isThereAtLeastOneAttributeConfiguredWithMeasurementFamily;

    public function setUp(): void
    {
        parent::setUp();
        $this->validator = $this->get('validator');
        $this->measurementFamilyRepository = $this->get('akeneo_measure.persistence.measurement_family_repository');
        $this->measurementFamilyRepository->clear();
        $this->deleteMeasurementFamilyHandler = $this->get('akeneo_measure.application.delete_measurement_family_handler');
        $this->isThereAtLeastOneAttributeConfiguredWithMeasurementFamily = $this->get('akeneo.pim.structure.query.is_there_at_least_one_attribute_configured_with_measurement_family');
        $this->eventDispatcherMock = $this->get('event_dispatcher');
    }

    /**
     * @test
     */
    public function it_deletes_an_existing_measurement_family(): void
    {
        $measurementFamilyCode = 'weight';
        $this->createMeasurementFamilyWithUnitsAndStandardUnit($measurementFamilyCode, ['KILOGRAM'], 'KILOGRAM');
        $this->isThereAtLeastOneAttributeConfiguredWithMeasurementFamily->setStub(false);

        $deleteCommand = new DeleteMeasurementFamilyCommand();
        $deleteCommand->code = $measurementFamilyCode;

        $violations = $this->validator->validate($deleteCommand);
        $this->deleteMeasurementFamilyHandler->handle($deleteCommand);

        $this->assertEquals(0, $violations->count());
        $this->assertMeasurementFamilyDeletedEventDispatched($measurementFamilyCode);
        $this->assertMeasurementFamilyDoesNotExists($measurementFamilyCode);
    }

    /**
     * @test
     */
    public function it_cannot_delete_an_existing_measurement_family_linked_to_a_product_attribute(): void
    {
        $measurementFamilyCode = 'weight';
        $this->createMeasurementFamilyWithUnitsAndStandardUnit($measurementFamilyCode, ['KILOGRAM'], 'KILOGRAM');
        $this->isThereAtLeastOneAttributeConfiguredWithMeasurementFamily->setStub(true);

        $deleteCommand = new DeleteMeasurementFamilyCommand();
        $deleteCommand->code = $measurementFamilyCode;

        $violations = $this->validator->validate($deleteCommand);

        $this->assertCannotRemoveTheMeasurementFamily($violations);
    }

    /**
     * @test
     */
    public function it_cannot_delete_a_measurement_family_that_does_not_exist(): void
    {
        $this->isThereAtLeastOneAttributeConfiguredWithMeasurementFamily->setStub(false);

        $deleteCommand = new DeleteMeasurementFamilyCommand();
        $deleteCommand->code = 'unknown_measurement_family';

        $violations = $this->validator->validate($deleteCommand);
        $this->expectException(MeasurementFamilyNotFoundException::class);
        $this->deleteMeasurementFamilyHandler->handle($deleteCommand);

        $this->assertEquals(0, $violations->count());
        $this->assertEmpty($this->eventDispatcherMock->getEvents());
    }

    private function assertMeasurementFamilyDeletedEventDispatched(string $measurementFamilyCode): void
    {
        $events = $this->eventDispatcherMock->getEvents();
        $this->assertCount(1, $events);
        $event = current($events)['event'];
        $this->assertInstanceOf(MeasurementFamilyDeleted::class, $event);
        $this->assertEquals($measurementFamilyCode, $event->getMeasurementFamilyCode()->normalize());
    }

    private function assertMeasurementFamilyDoesNotExists(string $measurementFamilyCode): void
    {
        try {
            $this->measurementFamilyRepository->getByCode(MeasurementFamilyCode::fromString($measurementFamilyCode));
        } catch (MeasurementFamilyNotFoundException $e) {
            return;
        }

        $this->fail(sprintf('Measurement family "%s" exists, expected not to exist', $measurementFamilyCode));
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

    /**
     * @param ConstraintViolationListInterface $violations
     */
    private function assertCannotRemoveTheMeasurementFamily(
        ConstraintViolationListInterface $violations
    ): void {
        $this->assertEquals(1, $violations->count());
        $violation = $violations->get(0);
        $this->assertEquals(
            'pim_measurements.validation.measurement_family.measurement_family_cannot_be_removed',
            $violation->getMessage()
        );
        $this->assertEquals('', $violation->getPropertyPath());
    }
}
