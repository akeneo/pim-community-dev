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

    public function __construct(
        array $config,
        MeasurementFamilyRepositoryInterface $measurementFamilyRepository
    ) {
        $this->config = $config['measures_config'];
        $this->measurementFamilyRepository = $measurementFamilyRepository;
    }

    public function getMeasurementFamilies(): array
    {
        return $this->config;
    }
}
