<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredExport\Application\Common\Operation;

use PhpSpec\ObjectBehavior;

class MeasurementConversionOperationSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith('a_measurement_family_code', 'a_target_unit_code');
    }

    public function it_returns_the_measurement_family_code(): void
    {
        $this->getMeasurementFamilyCode()->shouldReturn('a_measurement_family_code');
    }

    public function it_returns_the_target_unit_code(): void
    {
        $this->getTargetUnitCode()->shouldReturn('a_target_unit_code');
    }
}
