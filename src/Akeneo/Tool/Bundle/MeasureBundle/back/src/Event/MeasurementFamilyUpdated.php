<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Event;

use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MeasurementFamilyUpdated extends Event
{
    private MeasurementFamilyCode $measurementFamilyCode;

    public function __construct(MeasurementFamilyCode $measurementFamilyCode)
    {
        $this->measurementFamilyCode = $measurementFamilyCode;
    }

    public function getMeasurementFamilyCode(): MeasurementFamilyCode
    {
        return $this->measurementFamilyCode;
    }
}
