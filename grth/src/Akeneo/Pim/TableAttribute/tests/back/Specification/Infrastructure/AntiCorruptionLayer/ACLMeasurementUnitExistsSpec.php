<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\AntiCorruptionLayer;

use Akeneo\Pim\TableAttribute\Infrastructure\AntiCorruptionLayer\ACLMeasurementUnitExists;
use Akeneo\Tool\Bundle\MeasureBundle\ServiceApi\GetUnit;
use Akeneo\Tool\Bundle\MeasureBundle\ServiceApi\Unit;
use PhpSpec\ObjectBehavior;

class ACLMeasurementUnitExistsSpec extends ObjectBehavior
{
    function let(GetUnit $getUnit)
    {
        $this->beConstructedWith($getUnit);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ACLMeasurementUnitExists::class);
    }

    function it_returns_false_when_measurement_family_does_not_exist(GetUnit $getUnit)
    {
        $getUnit->byMeasurementFamilyCodeAndUnitCode('duration', 'meter')
            ->willThrow(new \Exception());

        $this->inFamily('duration', 'meter')->shouldReturn(false);
    }

    function it_returns_true_when_measurement_family_exists(GetUnit $getUnit)
    {
        $getUnit->byMeasurementFamilyCodeAndUnitCode('duration', 'second')->willReturn(new Unit());

        $this->inFamily('duration', 'second')->shouldReturn(true);
    }
}
