<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\MeasureBundle\Provider;

use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;

/**
 * @author    Valentin Dijkstra <valentin.dijkstra@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class LegacyMeasurementProvider
{
    /** @var array */
    protected $config;

    /** @var MeasurementFamilyRepositoryInterface */
    protected $measurementFamilyRepository;

    /** @var LegacyMeasurementAdapter */
    private $adapter;

    public function __construct(
        array $config,
        MeasurementFamilyRepositoryInterface $measurementFamilyRepository,
        LegacyMeasurementAdapter $adapter
    ) {
        $this->config = $config['measures_config'];
        $this->measurementFamilyRepository = $measurementFamilyRepository;
        $this->adapter = $adapter;
    }

    public function getMeasurementFamilies(): array
    {
        $measurementFamilies = array_map(function (MeasurementFamily $family) {
            return $this->adapter->adapts($family);
        }, iterator_to_array($this->measurementFamilyRepository->all()));

        $result = [];
        foreach ($measurementFamilies as $familyCode => $family) {
            $result = array_merge($result, $family);
        }

        return $result;
    }
}
