<?php

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Sorter\Attribute;

/**
 * Attribute base sorter for an Elasticsearch query
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseAttributeSorter extends AbstractAttributeSorter
{
    /**
     * {@inheritdoc}
     */
    protected function getAttributePathSuffix()
    {
        return null;
    }
}
