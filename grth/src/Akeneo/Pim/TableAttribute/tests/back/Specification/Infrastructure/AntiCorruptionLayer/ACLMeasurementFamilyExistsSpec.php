<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\AntiCorruptionLayer;

use Akeneo\Pim\TableAttribute\Infrastructure\AntiCorruptionLayer\ACLMeasurementFamilyExists;
use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ACLMeasurementFamilyExistsSpec extends ObjectBehavior
{
    function let(MeasurementFamilyRepositoryInterface $measurementFamilyRepository)
    {
        $this->beConstructedWith($measurementFamilyRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ACLMeasurementFamilyExists::class);
    }

    function it_returns_false_when_measurement_family_does_not_exist(
        MeasurementFamilyRepositoryInterface $measurementFamilyRepository
    ) {
        $measurementFamilyRepository->getByCode(MeasurementFamilyCode::fromString('unknown'))
            ->willThrow(new MeasurementFamilyNotFoundException());

        $this->forCode('unknown')->shouldReturn(false);
    }

    function it_returns_false_when_measurement_family_code_cannot_be_instantiated(
        MeasurementFamilyRepositoryInterface $measurementFamilyRepository
    ) {
        $measurementFamilyRepository->getByCode(Argument::any())->shouldNotBeCalled();

        $this->forCode('+=&*)')->shouldReturn(false);
    }

    function it_returns_true_when_measurement_family_exists(
        MeasurementFamilyRepositoryInterface $measurementFamilyRepository,
        MeasurementFamily $measurementFamily
    ) {
        $measurementFamilyRepository->getByCode(MeasurementFamilyCode::fromString('duration'))
            ->willReturn($measurementFamily);

        $this->forCode('duration')->shouldReturn(true);
    }
}
