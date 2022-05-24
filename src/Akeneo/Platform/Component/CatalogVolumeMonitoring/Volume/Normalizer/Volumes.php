<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Normalizer;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Volumes
{
    private array $countQueries = [];
    private array $averageMaxQueries = [];

    public function __construct(
        private CountVolumeNormalizer $countVolumesNormalizer,
        private AverageMaxVolumesNormalizer $averageMaxVolumesNormalizer,
        private FeatureFlags $featureFlags
    ) {
    }

    /**
     * Returns an array containing the volume values of the different entities.
     *
     */
    public function volumes(): array
    {
        $data = [];

        foreach ($this->countQueries as $query) {
            if (null !== $query['feature'] && !$this->featureFlags->isEnabled($query['feature'])) {
                continue;
            }

            $queryResponse = $query['query']->fetch();
            $data = array_merge($data, $this->countVolumesNormalizer->normalize($queryResponse));
        }

        foreach ($this->averageMaxQueries as $query) {
            if (null !== $query['feature'] && !$this->featureFlags->isEnabled($query['feature'])) {
                continue;
            }

            $queryResponse = $query['query']->fetch();
            $data = array_merge($data, $this->averageMaxVolumesNormalizer->normalize($queryResponse));
        }

        return $data;
    }

    public function addCountVolumeQuery(CountQuery $query, ?string $feature): void
    {
        $this->countQueries[] = [
            'query' => $query,
            'feature' => $feature
        ];
    }

    public function addAverageMaxVolumeQuery(AverageMaxQuery $query, ?string $feature): void
    {
        $this->averageMaxQueries[] = [
            'query' => $query,
            'feature' => $feature
        ];
    }
}
