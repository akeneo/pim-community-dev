<?php

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Sorter\Attribute;

/**
 * Text area sorter for an Elastic search query
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TextAreaSorter extends AbstractAttributeSorter
{
    /**
     * {@inheritdoc}
     */
    protected function getAttributePathSuffix()
    {
        return 'preprocessed';
    }
}
