<?php
declare(strict_types=1);

namespace Pim\Bundle\AnalyticsBundle\DataCollector;

use Akeneo\Component\Analytics\DataCollectorInterface;
use Akeneo\Component\StorageUtils\Repository\CountableRepositoryInterface;
use Pim\Bundle\AnalyticsBundle\Doctrine\Query;
use Pim\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Pim\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;

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

    /**
     * @merge TODO - on master - remove '= null'
     * @param CountQuery        $attributeCountQuery
     * @param CountQuery        $localizableAttributeCountQuery
     * @param CountQuery        $scopableAttributeCountQuery
     * @param CountQuery        $localizableAndScopableAttributeCountQuery
     * @param CountQuery        $useableAsGridFilterAttributeCountQuery
     * @param AverageMaxQuery   $localizableAttributePerFamilyAverageMaxQuery
     * @param AverageMaxQuery   $scopableAttributePerFamilyAverageMaxQuery
     * @param AverageMaxQuery   $localizableAndScopableAttributePerFamilyAverageMaxQuery
     */
    public function __construct(
        CountQuery $attributeCountQuery,
        CountQuery $localizableAttributeCountQuery,
        CountQuery $scopableAttributeCountQuery,
        CountQuery $localizableAndScopableAttributeCountQuery,
        CountQuery $useableAsGridFilterAttributeCountQuery = null,
        AverageMaxQuery $localizableAttributePerFamilyAverageMaxQuery = null,
        AverageMaxQuery $scopableAttributePerFamilyAverageMaxQuery = null,
        AverageMaxQuery $localizableAndScopableAttributePerFamilyAverageMaxQuery = null
    ) {
        $this->attributeCountQuery = $attributeCountQuery;
        $this->localizableAttributeCountQuery = $localizableAttributeCountQuery;
        $this->scopableAttributeCountQuery = $scopableAttributeCountQuery;
        $this->localizableAndScopableAttributeCountQuery = $localizableAndScopableAttributeCountQuery;
        $this->useableAsGridFilterAttributeCountQuery = $useableAsGridFilterAttributeCountQuery;

        $this->localizableAttributePerFamilyAverageMaxQuery = $localizableAttributePerFamilyAverageMaxQuery;
        $this->scopableAttributePerFamilyAverageMaxQuery = $scopableAttributePerFamilyAverageMaxQuery;
        $this->localizableAndScopableAttributePerFamilyAverageMaxQuery = $localizableAndScopableAttributePerFamilyAverageMaxQuery;
    }

    /**
     * @merge TODO - on master - remove the if statements & move all inside the "if session" inside $data
     * {@inheritdoc}
     */
    public function collect(): array
    {
        $numberOfUseableAsGridFilterAttribute = 0;
        if (null !== $this->useableAsGridFilterAttributeCountQuery) {
            $numberOfUseableAsGridFilterAttribute = $this->useableAsGridFilterAttributeCountQuery->fetch()->getVolume();
        }

        $numberOfScopableAttributePerFamilyAverageMaxQuery = 0;
        if (null !== $this->scopableAttributePerFamilyAverageMaxQuery) {
            $numberOfScopableAttributePerFamilyAverageMaxQuery = $this->scopableAttributePerFamilyAverageMaxQuery->fetch()->getAverageVolume();
        }

        $numberOfLocalizableAttributePerFamilyAverageMaxQuery = 0;
        if (null !== $this->localizableAttributePerFamilyAverageMaxQuery) {
            $numberOfLocalizableAttributePerFamilyAverageMaxQuery = $this->localizableAttributePerFamilyAverageMaxQuery->fetch()->getAverageVolume();
        }

        $numberOfLocalizableAndScopableAttributePerFamilyAverageMaxQuery = 0;
        if (null !== $this->localizableAndScopableAttributePerFamilyAverageMaxQuery) {
            $numberOfLocalizableAndScopableAttributePerFamilyAverageMaxQuery = $this->localizableAndScopableAttributePerFamilyAverageMaxQuery->fetch()->getAverageVolume();
        }

        $data = [
            'nb_attributes' => $this->attributeCountQuery->fetch()->getVolume(),
            'nb_scopable_attributes' => $this->scopableAttributeCountQuery->fetch()->getVolume(),
            'nb_localizable_attributes' => $this->localizableAttributeCountQuery->fetch()->getVolume(),
            'nb_scopable_localizable_attributes' => $this->localizableAndScopableAttributeCountQuery->fetch()->getVolume(),
            'nb_useable_as_grid_filter_attributes' => $numberOfUseableAsGridFilterAttribute,
            'avg_percentage_scopable_attributes_per_family' => $numberOfScopableAttributePerFamilyAverageMaxQuery,
            'avg_percentage_localizable_attributes_per_family' => $numberOfLocalizableAttributePerFamilyAverageMaxQuery,
            'avg_percentage_scopable_localizable_attributes_per_family' => $numberOfLocalizableAndScopableAttributePerFamilyAverageMaxQuery,
        ];

        return $data;
    }
}
