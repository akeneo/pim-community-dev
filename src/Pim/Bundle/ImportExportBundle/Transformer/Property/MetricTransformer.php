<?php

namespace Pim\Bundle\ImportExportBundle\Transformer\Property;

use Hermes\Bundle\ImportExportBundle\Transformer\Property\EntityUpdaterInterface;
use InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\Metric;
use Pim\Bundle\ImportExportBundle\Exception\PropertyTransformerException;
use Pim\Bundle\ImportExportBundle\Transformer\ColumnInfo\ColumnInfoInterface;

/**
 * Metric attribute transformer
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricTransformer extends DefaultTransformer implements EntityUpdaterInterface
{
    public function setValue($object, ColumnInfoInterface $columnInfo, $data, array $options = array())
    {
        $suffixes = $columnInfo->getSuffixes();
        
    }
}
