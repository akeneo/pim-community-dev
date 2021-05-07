<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\PublicApi\Onboarder;

use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily as MeasurementFamilyAggregate;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindAllMeasurementFamilies
{
    private MeasurementFamilyRepositoryInterface $measurementFamilyRepository;

    public function __construct(MeasurementFamilyRepositoryInterface $measurementFamilyRepository)
    {
        $this->measurementFamilyRepository = $measurementFamilyRepository;
    }

    /**
     * @return MeasurementFamily[]
     */
    public function execute(): array
    {
        $allMeasurementFamilies = $this->measurementFamilyRepository->all();

        return array_map(
            static fn (MeasurementFamilyAggregate $measurementFamily) => MeasurementFamily::fromAggregate($measurementFamily),
            $allMeasurementFamilies
        );
    }
}
