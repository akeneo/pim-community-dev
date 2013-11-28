<?php

namespace Pim\Bundle\ImportExportBundle\Transformer\Property;

use Pim\Bundle\CatalogBundle\Model\ProductPrice;
use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\ImportExportBundle\Exception\PropertyTransformerException;
use Pim\Bundle\ImportExportBundle\Transformer\ColumnInfo\ColumnInfoInterface;

/**
 * Prices attribute transformer
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PricesTransformer implements PropertyTransformerInterface, EntityUpdaterInterface
{
    /**
     * @var CurrencyManager
     */
    protected $currencyManager;

    /**
     * @var array
     */
    private $currencies;

    /**
     * Constructor
     *
     * @param CurrencyManager $currencyManager
     */
    public function __construct(CurrencyManager $currencyManager)
    {
        $this->currencyManager = $currencyManager;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value, array $options = array())
    {
        $currencies = $this->getCurrencies();

        $result = array();
        foreach (preg_split('/\s*,\s*/', trim($value)) as $price) {
            if (empty($price)) {
                continue;
            }

            if (0 === preg_match('/^([^\s]+) (\w+)$/', $price, $matches)) {
                throw new PropertyTransformerException('Malformed price: "%value%"', array('%value%' => $price));
            }

            if (!in_array($matches[2], $currencies)) {
                throw new PropertyTransformerException(
                    'Currency "%currency%" is not active',
                    array('%currency%' => $matches[2])
                );
            }

            $result[$matches[2]] = $this->createPrice($matches[1], $matches[2]);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */

    public function setValue($object, ColumnInfoInterface $columnInfo, $data, array $options = array())
    {
        $currencies = $this->getCurrencies();
        $removeCurrency = function ($code) use (&$currencies) {
            $pos = array_search($code, $currencies);
            if (false !== $pos) {
                unset($currencies[$pos]);
            }
        };

        foreach ($object->getPrices() as $price) {
            $currency = $price->getCurrency();
            if (isset($data[$currency])) {
                $price->setData($data[$currency]->getData());
                $removeCurrency($currency);
                unset($data[$currency]);
            }
        }

        foreach ($data as $currency => $price) {
            $this->addPrice($object, $price->getData(), $currency);
            $removeCurrency($currency);
        }

        foreach ($currencies as $currency) {
            $this->addPrice($object, null, $currency);
        }
    }

    /**
     * Returns the active currencies
     *
     * @return array
     */
    protected function getCurrencies()
    {
        if (!isset($this->currencies)) {
            $this->currencies = $this->currencyManager->getActiveCodes();
        }

        return $this->currencies;
    }

    /**
     * Creates a ProductPrice object and adds it to the product value
     *
     * @param ProductValueInterface $productValue
     * @param float                 $data
     * @param string                $currency
     *
     * @return ProductPrice
     */
    protected function addPrice(ProductValueInterface $productValue, $data, $currency)
    {
        $productValue->addPrice($this->createPrice($data, $currency)->setValue($productValue));
    }

    /**
     * Creates a ProductPrice object
     *
     * @param float  $data
     * @param string $currency
     *
     * @return ProductPrice
     */
    protected function createPrice($data, $currency)
    {
        $price = new ProductPrice();
        $price->setData($data)->setCurrency($currency);

        return $price;
    }
}
