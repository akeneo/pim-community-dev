<?php

namespace Pim\Bundle\ImportExportBundle\Transformer\Property;

use Pim\Bundle\ImportExportBundle\Exception\PropertyTransformerException;
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

    public function setValue($object, ColumnInfoInterface $columnInfo, $data, array $options = [])
    {
        $suffixes = $columnInfo->getSuffixes();
        $currency = array_pop($suffixes);

        if (null === $currency) {
            if (null === $data) {
                $data = [];
            } elseif (is_string($data)) {
                $data = $this->parseFlatPrices($data);
            }
            $object->setPrices([]);
            foreach ($data as $currency => $value) {
                $object->addPriceForCurrency($currency)->setData($value);
            }
        } else {
            $object->addPriceForCurrency($currency)->setData($data);
        }
    }

    /**
     * Parses a string representation of prices and returns an array containing the currency as key
     *
     * @param string $data
     *
     * @return array
     */
    protected function parseFlatPrices($data)
    {
        $prices = [];
        foreach (preg_split('/\s*,\s*/', $data) as $price) {
            $parts = preg_split('/\s+/', $price);
            if (count($parts) > 1) {
                $prices[$parts[1]] = $parts[0];
            } else {
                throw new PropertyTransformerException('Malformed price: "%price%"', ['%price%' => $price]);
            }
        }

        return $prices;
    }
}
