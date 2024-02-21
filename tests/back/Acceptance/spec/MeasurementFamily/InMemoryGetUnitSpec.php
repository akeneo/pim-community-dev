<?php

declare(strict_types=1);

namespace spec\Akeneo\Test\Acceptance\MeasurementFamily;

use Akeneo\Test\Acceptance\MeasurementFamily\InMemoryGetUnit;
use Akeneo\Test\Acceptance\MeasurementFamily\InMemoryMeasurementFamilyRepository;
use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Model\LabelCollection;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Operation;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Unit;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use Akeneo\Tool\Bundle\MeasureBundle\ServiceApi\GetUnit;
use Akeneo\Tool\Bundle\MeasureBundle\ServiceApi\Unit as PublicUnit;
use PhpSpec\ObjectBehavior;

class InMemoryGetUnitSpec extends ObjectBehavior
{
    function let(InMemoryMeasurementFamilyRepository $measurementFamilyRepository)
    {
        $measurementFamilyRepository->getByCode(MeasurementFamilyCode::fromString('duration'))
            ->willReturn(MeasurementFamily::create(
                MeasurementFamilyCode::fromString('duration'),
                LabelCollection::fromArray(['en_US' => 'Duration', 'fr_FR' => 'Durée']),
                UnitCode::fromString('second'),
                [
                    Unit::create(
                        UnitCode::fromString('second'),
                        LabelCollection::fromArray(['en_US' => 'second']),
                        [
                            Operation::create('mul', '1'),
                        ],
                        's',
                    ),
                ]));

        $this->beConstructedWith($measurementFamilyRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(InMemoryGetUnit::class);
        $this->shouldImplement(GetUnit::class);
    }

    function it_throws_an_exception_when_measurement_family_is_unknown(InMemoryMeasurementFamilyRepository $measurementFamilyRepository)
    {
        $measurementFamilyRepository->getByCode(MeasurementFamilyCode::fromString('unknown'))
            ->willThrow(new MeasurementFamilyNotFoundException());

        $this->shouldThrow(\Exception::class)->during('byMeasurementFamilyCodeAndUnitCode', ['unknown', 'second']);
    }

    function it_throws_an_exception_when_unit_is_unknown()
    {
        $this->shouldThrow(\Exception::class)->during('byMeasurementFamilyCodeAndUnitCode', ['duration', 'unknown']);
    }

    function it_returns_the_unit()
    {
        $expectedUnit = new PublicUnit();
        $expectedUnit->code = 'second';
        $expectedUnit->labels = ['en_US' => 'second'];
        $expectedUnit->convertFromStandard = [
            ['operator' => 'mul', 'value' => '1']
        ];
        $expectedUnit->symbol = 's';

        $this->byMeasurementFamilyCodeAndUnitCode('duration', 'second')->shouldBeLike($expectedUnit);
        $this->byMeasurementFamilyCodeAndUnitCode('duration', 'SeCoNd')->shouldBeLike($expectedUnit);
    }
}
