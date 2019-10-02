<?php

namespace Akeneo\Platform\Bundle\AnalyticsBundle\DataCollector;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;
use Akeneo\Tool\Component\Analytics\DataCollectorInterface;
use Akeneo\Tool\Component\Analytics\EmailDomainsQuery;

/**
 * Collects the structure of the PIM catalog:
 * - number of channels
 * - number of products
 * - number of attributes
 * - number of locales
 * - number of families
 * - number of users
 * - number of categories
 * - number of categories tree
 * - max number of categories in one category
 * - max number of category levels
 * - number of product values
 * - average number of product values by product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DBDataCollector implements DataCollectorInterface
{
    /** @var CountQuery */
    protected $channelCountQuery;

    /** @var CountQuery */
    protected $productCountQuery;

    /** @var CountQuery */
    protected $localeCountQuery;

    /** @var CountQuery */
    protected $familyCountQuery;

    /** @var CountQuery */
    protected $userCountQuery;

    /** @var CountQuery */
    protected $productModelCountQuery;

    /** @var CountQuery */
    protected $variantProductCountQuery;

    /** @var CountQuery */
    protected $categoryCountQuery;

    /** @var CountQuery */
    protected $categoryTreeCountQuery;

    /** @var CountQuery */
    protected $productValueCountQuery;

    /** @var AverageMaxQuery */
    protected $productValueAverageMaxQuery;

    /** @var AverageMaxQuery */
    protected $categoryLevelsAverageMax;

    /** @var AverageMaxQuery */
    protected $categoriesInOneCategoryAverageMax;

    /** @var AverageMaxQuery */
    protected $productValuePerFamilyAverageMaxQuery;

    /** @var EmailDomainsQuery */
    protected $emailDomains;

    public function __construct(
        CountQuery        $channelCountQuery,
        CountQuery        $productCountQuery,
        CountQuery        $localeCountQuery,
        CountQuery        $familyCountQuery,
        CountQuery        $userCountQuery,
        CountQuery        $productModelCountQuery,
        CountQuery        $variantProductCountQuery,
        CountQuery        $categoryCountQuery,
        CountQuery        $categoryTreeCountQuery,
        AverageMaxQuery   $categoriesInOneCategoryAverageMax,
        AverageMaxQuery   $categoryLevelsAverageMax,
        CountQuery        $productValueCountQuery,
        AverageMaxQuery   $productValueAverageMaxQuery,
        AverageMaxQuery   $productValuePerFamilyAverageMaxQuery,
        EmailDomainsQuery $emailDomains
    ) {
        $this->channelCountQuery = $channelCountQuery;
        $this->productCountQuery = $productCountQuery;
        $this->productModelCountQuery = $productModelCountQuery;
        $this->variantProductCountQuery = $variantProductCountQuery;
        $this->localeCountQuery = $localeCountQuery;
        $this->familyCountQuery = $familyCountQuery;
        $this->userCountQuery = $userCountQuery;
        $this->categoryCountQuery = $categoryCountQuery;
        $this->categoriesInOneCategoryAverageMax = $categoriesInOneCategoryAverageMax;
        $this->categoryLevelsAverageMax = $categoryLevelsAverageMax;
        $this->categoryTreeCountQuery = $categoryTreeCountQuery;
        $this->productValueCountQuery = $productValueCountQuery;
        $this->productValueAverageMaxQuery = $productValueAverageMaxQuery;
        $this->productValuePerFamilyAverageMaxQuery = $productValuePerFamilyAverageMaxQuery;
        $this->emailDomains = $emailDomains;
    }

    /**
     * {@inheritdoc}
     */
    public function collect()
    {
        return [
            'nb_channels'                    => $this->channelCountQuery->fetch()->getVolume(),
            'nb_locales'                     => $this->localeCountQuery->fetch()->getVolume(),
            'nb_products'                    => $this->productCountQuery->fetch()->getVolume(),
            'nb_product_models'              => $this->productModelCountQuery->fetch()->getVolume(),
            'nb_variant_products'            => $this->variantProductCountQuery->fetch()->getVolume(),
            'nb_families'                    => $this->familyCountQuery->fetch()->getVolume(),
            'nb_users'                       => $this->userCountQuery->fetch()->getVolume(),
            'nb_categories'                  => $this->categoryCountQuery->fetch()->getVolume(),
            'nb_category_trees'              => $this->categoryTreeCountQuery->fetch()->getVolume(),
            'max_category_in_one_category'   => $this->categoriesInOneCategoryAverageMax->fetch()->getMaxVolume(),
            'max_category_levels'            => $this->categoryLevelsAverageMax->fetch()->getMaxVolume(),
            'nb_product_values'              => $this->productValueCountQuery->fetch()->getVolume(),
            'avg_product_values_by_product'  => $this->productValueAverageMaxQuery->fetch()->getAverageVolume(),
            'avg_product_values_by_family'  => $this->productValuePerFamilyAverageMaxQuery->fetch()->getAverageVolume(),
            'max_product_values_by_family'  => $this->productValuePerFamilyAverageMaxQuery->fetch()->getMaxVolume(),
            'email_domains' => $this->emailDomains->fetch()
        ];
    }
}
