<?php

namespace Akeneo\Platform\Bundle\AnalyticsBundle\DataCollector;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;
use Akeneo\Tool\Component\Analytics\ActiveEventSubscriptionCountQuery;
use Akeneo\Tool\Component\Analytics\ApiConnectionCountQuery;
use Akeneo\Tool\Component\Analytics\DataCollectorInterface;
use Akeneo\Tool\Component\Analytics\EmailDomainsQuery;
use Akeneo\Tool\Component\Analytics\IsDemoCatalogQuery;
use Akeneo\Tool\Component\Analytics\MediaCountQuery;

/**
 * It collect data about the volume of different axes in the PIM catalog.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DBDataCollector implements DataCollectorInterface
{
    private CountQuery $channelCountQuery;

    private CountQuery $productCountQuery;

    private CountQuery $localeCountQuery;

    private CountQuery $familyCountQuery;

    private CountQuery $attributeCountQuery;

    private CountQuery $userCountQuery;

    private CountQuery $productModelCountQuery;

    private CountQuery $variantProductCountQuery;

    private CountQuery $categoryCountQuery;

    private CountQuery $categoryTreeCountQuery;

    private CountQuery $productValueCountQuery;

    private AverageMaxQuery $productValueAverageMaxQuery;

    private AverageMaxQuery $categoryLevelsAverageMax;

    private AverageMaxQuery $categoriesInOneCategoryAverageMax;

    private AverageMaxQuery$productValuePerFamilyAverageMaxQuery;

    private EmailDomainsQuery $emailDomains;

    private ApiConnectionCountQuery $apiConnectionCountQuery;

    private MediaCountQuery $mediaCountQuery;

    private IsDemoCatalogQuery $isDemoCatalogQuery;

    private ActiveEventSubscriptionCountQuery $activeEventSubscriptionCountQuery;

    public function __construct(
        CountQuery $channelCountQuery,
        CountQuery $productCountQuery,
        CountQuery $localeCountQuery,
        CountQuery $familyCountQuery,
        CountQuery $attributeCountQuery,
        CountQuery $userCountQuery,
        CountQuery $productModelCountQuery,
        CountQuery $variantProductCountQuery,
        CountQuery $categoryCountQuery,
        CountQuery $categoryTreeCountQuery,
        AverageMaxQuery $categoriesInOneCategoryAverageMax,
        AverageMaxQuery $categoryLevelsAverageMax,
        CountQuery $productValueCountQuery,
        AverageMaxQuery $productValueAverageMaxQuery,
        AverageMaxQuery $productValuePerFamilyAverageMaxQuery,
        EmailDomainsQuery $emailDomains,
        ApiConnectionCountQuery $apiConnectionCountQuery,
        MediaCountQuery $mediaCountQuery,
        IsDemoCatalogQuery $isDemoCatalogQuery,
        ActiveEventSubscriptionCountQuery $activeEventSubscriptionCountQuery
    ) {
        $this->channelCountQuery = $channelCountQuery;
        $this->productCountQuery = $productCountQuery;
        $this->productModelCountQuery = $productModelCountQuery;
        $this->variantProductCountQuery = $variantProductCountQuery;
        $this->localeCountQuery = $localeCountQuery;
        $this->familyCountQuery = $familyCountQuery;
        $this->attributeCountQuery = $attributeCountQuery;
        $this->userCountQuery = $userCountQuery;
        $this->categoryCountQuery = $categoryCountQuery;
        $this->categoriesInOneCategoryAverageMax = $categoriesInOneCategoryAverageMax;
        $this->categoryLevelsAverageMax = $categoryLevelsAverageMax;
        $this->categoryTreeCountQuery = $categoryTreeCountQuery;
        $this->productValueCountQuery = $productValueCountQuery;
        $this->productValueAverageMaxQuery = $productValueAverageMaxQuery;
        $this->productValuePerFamilyAverageMaxQuery = $productValuePerFamilyAverageMaxQuery;
        $this->emailDomains = $emailDomains;
        $this->apiConnectionCountQuery = $apiConnectionCountQuery;
        $this->mediaCountQuery = $mediaCountQuery;
        $this->isDemoCatalogQuery = $isDemoCatalogQuery;
        $this->activeEventSubscriptionCountQuery = $activeEventSubscriptionCountQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function collect()
    {
        return [
            'nb_channels' => $this->channelCountQuery->fetch()->getVolume(),
            'nb_locales' => $this->localeCountQuery->fetch()->getVolume(),
            'nb_products' => $this->productCountQuery->fetch()->getVolume(),
            'nb_product_models' => $this->productModelCountQuery->fetch()->getVolume(),
            'nb_variant_products' => $this->variantProductCountQuery->fetch()->getVolume(),
            'nb_families' => $this->familyCountQuery->fetch()->getVolume(),
            'nb_attributes' => $this->attributeCountQuery->fetch()->getVolume(),
            'nb_users' => $this->userCountQuery->fetch()->getVolume(),
            'nb_categories' => $this->categoryCountQuery->fetch()->getVolume(),
            'nb_category_trees' => $this->categoryTreeCountQuery->fetch()->getVolume(),
            'max_category_in_one_category' => $this->categoriesInOneCategoryAverageMax->fetch()->getMaxVolume(),
            'max_category_levels' => $this->categoryLevelsAverageMax->fetch()->getMaxVolume(),
            'nb_product_values' => $this->productValueCountQuery->fetch()->getVolume(),
            'avg_product_values_by_product' => $this->productValueAverageMaxQuery->fetch()->getAverageVolume(),
            'avg_product_values_by_family' => $this->productValuePerFamilyAverageMaxQuery->fetch()->getAverageVolume(),
            'max_product_values_by_family' => $this->productValuePerFamilyAverageMaxQuery->fetch()->getMaxVolume(),
            'email_domains' => $this->emailDomains->fetch(),
            'api_connection' => $this->apiConnectionCountQuery->fetch(),
            'nb_media_files_in_products' => $this->mediaCountQuery->countFiles(),
            'nb_media_images_in_products' => $this->mediaCountQuery->countImages(),
            'is_demo_catalog' => $this->isDemoCatalogQuery->fetch(),
            'nb_active_event_subscription' => $this->activeEventSubscriptionCountQuery->fetch(),
        ];
    }
}
