<?php

namespace Pim\Bundle\CatalogBundle\Elasticsearch\Sorter\Attributes;

/**
 * Metric sorter for an Elastic search query
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricSorter extends AbstractAttributeSorter
{
    /**
     * {@inheritdoc}
     */
    protected function getAttributePathSuffix()
    {
        return 'base_data';
    }
}
