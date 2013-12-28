<?php

namespace Pim\Bundle\ImportExportBundle\Transformer\Property;

use Pim\Bundle\ImportExportBundle\Transformer\ColumnInfo\ColumnInfoInterface;

/**
 * Prices attribute transformer
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PricesTransformer extends DefaultTransformer implements EntityUpdaterInterface
{
    /**
     * {@inheritdoc}
     */

    public function setValue($object, ColumnInfoInterface $columnInfo, $data, array $options = array())
    {
        $suffixes = $columnInfo->getSuffixes();
        $currency = array_pop($suffixes);
        if (null === $currency && null === $data) {
            $data = array();
        }
        if (is_array($data)) {
            foreach ($object->getPrices() as $price) {
                $price->setData(null);
            }
            foreach ($data as $currency => $value) {
                $object->addPriceForCurrency($currency)->setData($value);
            }
        } else {
            $object->addPriceForCurrency($currency)->setData($value);
        }
    }
}
