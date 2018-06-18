<?php
declare(strict_types=1);

namespace Pim\Bundle\AnalyticsBundle\DataCollector;

use Akeneo\Component\Analytics\DataCollectorInterface;
use Akeneo\Component\StorageUtils\Repository\CountableRepositoryInterface;
use Pim\Bundle\AnalyticsBundle\Doctrine\Query;
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

    /**
     * @param CountQuery        $attributeCountQuery
     * @param CountQuery        $localizableAttributeCountQuery
     * @param CountQuery        $scopableAttributeCountQuery
     * @param CountQuery        $localizableAndScopableAttributeCountQuery
     */
    public function __construct(
        CountQuery $attributeCountQuery,
        CountQuery $localizableAttributeCountQuery,
        CountQuery $scopableAttributeCountQuery,
        CountQuery $localizableAndScopableAttributeCountQuery
    ) {
        $this->attributeCountQuery = $attributeCountQuery;
        $this->localizableAttributeCountQuery = $localizableAttributeCountQuery;
        $this->scopableAttributeCountQuery = $scopableAttributeCountQuery;
        $this->localizableAndScopableAttributeCountQuery = $localizableAndScopableAttributeCountQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(): array
    {
        $data = [
            'nb_attributes' => $this->attributeCountQuery->fetch()->getVolume(),
            'nb_scopable_attributes' => $this->scopableAttributeCountQuery->fetch()->getVolume(),
            'nb_localizable_attributes' => $this->localizableAttributeCountQuery->fetch()->getVolume(),
            'nb_scopable_localizable_attributes' => $this->localizableAndScopableAttributeCountQuery->fetch()->getVolume(),
        ];

        return $data;
    }
}
