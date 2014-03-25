<?php

namespace Pim\Bundle\TransformBundle\Transformer\Property;

use Pim\Bundle\TransformBundle\Exception\PropertyTransformerException;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoInterface;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;

/**
 * Prices attribute transformer
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PricesTransformer extends DefaultTransformer implements EntityUpdaterInterface
{
    protected $builder;

    public function __construct(ProductBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($object, ColumnInfoInterface $columnInfo, $data, array $options = array())
    {
        $suffixes = $columnInfo->getSuffixes();
        $currency = array_pop($suffixes);

        if (null === $currency) {
            if (null === $data) {
                $data = array();
            } elseif (is_string($data)) {
                $data = $this->parseFlatPrices($data);
            }

            $this->builder->removePricesNotInCurrency($object, array_keys($data));
            foreach ($data as $currency => $value) {
                $this->builder->addPriceForCurrency($object, $currency)->setData($value);
            }
        } else {
            $this->builder->addPriceForCurrency($object, $currency)->setData($data);
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
        $prices = array();
        foreach (explode(',', $data) as $price) {
            $parts = explode(' ', trim($price));
            if (count($parts) > 1) {
                $prices[$parts[1]] = $parts[0];
            } else {
                throw new PropertyTransformerException('Malformed price: "%price%"', array('%price%' => $price));
            }
        }

        return $prices;
    }
}
