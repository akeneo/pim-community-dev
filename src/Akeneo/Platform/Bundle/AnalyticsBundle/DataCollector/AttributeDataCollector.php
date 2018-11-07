<?php
declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AnalyticsBundle\DataCollector;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;
use Akeneo\Tool\Component\Analytics\DataCollectorInterface;

/**
 * Collect data about attributes:
 *  - number of scopable attribute
 *  - number of localizable attribute
 *  - number of localizable and scopable attribute
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeDataCollector implements DataCollectorInterface
{
    /** @var CountQuery */
    private $attributeCountQuery;

    /** @var CountQuery */
    private $localizableAttributeCountQuery;

    /** @var CountQuery */
    private $scopableAttributeCountQuery;

    /** @var CountQuery */
    private $localizableAndScopableAttributeCountQuery;

    /** @var CountQuery */
    private $useableAsGridFilterAttributeCountQuery;

    /** @var AverageMaxQuery */
    private $localizableAttributePerFamilyAverageMaxQuery;

    /** @var AverageMaxQuery */
    private $scopableAttributePerFamilyAverageMaxQuery;

    /** @var AverageMaxQuery */
    private $localizableAndScopableAttributePerFamilyAverageMaxQuery;

    /** @var AverageMaxQuery */
    private $attributePerFamilyAverageMaxQuery;

    /**
     * @param CountQuery        $attributeCountQuery
     * @param CountQuery        $localizableAttributeCountQuery
     * @param CountQuery        $scopableAttributeCountQuery
     * @param CountQuery        $localizableAndScopableAttributeCountQuery
     * @param CountQuery        $useableAsGridFilterAttributeCountQuery
     * @param AverageMaxQuery   $localizableAttributePerFamilyAverageMaxQuery
     * @param AverageMaxQuery   $scopableAttributePerFamilyAverageMaxQuery
     * @param AverageMaxQuery   $localizableAndScopableAttributePerFamilyAverageMaxQuery
     * @param AverageMaxQuery   $attributePerFamilyAverageMaxQuery
     *
     */
    public function __construct(
        CountQuery $attributeCountQuery,
        CountQuery $localizableAttributeCountQuery,
        CountQuery $scopableAttributeCountQuery,
        CountQuery $localizableAndScopableAttributeCountQuery,
        CountQuery $useableAsGridFilterAttributeCountQuery,
        AverageMaxQuery $localizableAttributePerFamilyAverageMaxQuery,
        AverageMaxQuery $scopableAttributePerFamilyAverageMaxQuery,
        AverageMaxQuery $localizableAndScopableAttributePerFamilyAverageMaxQuery,
        AverageMaxQuery $attributePerFamilyAverageMaxQuery
    ) {
        $this->attributeCountQuery = $attributeCountQuery;
        $this->localizableAttributeCountQuery = $localizableAttributeCountQuery;
        $this->scopableAttributeCountQuery = $scopableAttributeCountQuery;
        $this->localizableAndScopableAttributeCountQuery = $localizableAndScopableAttributeCountQuery;
        $this->useableAsGridFilterAttributeCountQuery = $useableAsGridFilterAttributeCountQuery;

        $this->localizableAttributePerFamilyAverageMaxQuery = $localizableAttributePerFamilyAverageMaxQuery;
        $this->scopableAttributePerFamilyAverageMaxQuery = $scopableAttributePerFamilyAverageMaxQuery;
        $this->localizableAndScopableAttributePerFamilyAverageMaxQuery = $localizableAndScopableAttributePerFamilyAverageMaxQuery;
        $this->attributePerFamilyAverageMaxQuery = $attributePerFamilyAverageMaxQuery;
    }

    /**
     * @return array
     */
    public function collect(): array
    {
        $data = [
            'nb_attributes' => $this->attributeCountQuery->fetch()->getVolume(),
            'nb_scopable_attributes' => $this->scopableAttributeCountQuery->fetch()->getVolume(),
            'nb_localizable_attributes' => $this->localizableAttributeCountQuery->fetch()->getVolume(),
            'nb_scopable_localizable_attributes' => $this->localizableAndScopableAttributeCountQuery->fetch()->getVolume(),
            'nb_useable_as_grid_filter_attributes' => $this->useableAsGridFilterAttributeCountQuery->fetch()->getVolume(),
            'avg_percentage_scopable_attributes_per_family' => $this->scopableAttributePerFamilyAverageMaxQuery->fetch()->getAverageVolume(),
            'avg_percentage_localizable_attributes_per_family' => $this->localizableAttributePerFamilyAverageMaxQuery->fetch()->getAverageVolume(),
            'avg_percentage_scopable_localizable_attributes_per_family' => $this->localizableAndScopableAttributePerFamilyAverageMaxQuery->fetch()->getAverageVolume(),
            'avg_number_attributes_per_family' => $this->attributePerFamilyAverageMaxQuery->fetch()->getAverageVolume(),
        ];

        return $data;
    }
}
