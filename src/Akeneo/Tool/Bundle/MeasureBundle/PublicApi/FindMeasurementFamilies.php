<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\PublicApi;

use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily as MeasurementFamilyAggregate;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindMeasurementFamilies
{
    private MeasurementFamilyRepositoryInterface $measurementFamilyRepository;

    public function __construct(MeasurementFamilyRepositoryInterface $measurementFamilyRepository)
    {
        $this->measurementFamilyRepository = $measurementFamilyRepository;
    }

    /**
     * @return MeasurementFamily[]
     */
    public function all(): array
    {
        $allMeasurementFamilies = $this->measurementFamilyRepository->all();

        return array_map(
            static fn (MeasurementFamilyAggregate $measurementFamily) => MeasurementFamily::fromAggregate($measurementFamily),
            $allMeasurementFamilies
        );
    }

    /**
     * @param string $code
     * @return MeasurementFamily
     * @throws MeasurementFamilyNotFoundException
     */
    public function getByCode(string $code): MeasurementFamily
    {
        try {
            $measurementFamily = $this->measurementFamilyRepository->getByCode(MeasurementFamilyCode::fromString($code));
        } catch (MeasurementFamilyNotFoundException $e) {
            throw new MeasurementFamilyNotFoundException();
        }

        return MeasurementFamily::fromAggregate($measurementFamily);
    }
}
