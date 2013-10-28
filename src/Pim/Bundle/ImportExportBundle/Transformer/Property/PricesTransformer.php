<?php

namespace Pim\Bundle\ImportExportBundle\Transformer\Property;

use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;
use Pim\Bundle\ImportExportBundle\Exception\InvalidValueException;

/**
 * Prices attribute transformer
 * 
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PricesTransformer implements PropertyTransformerInterface
{
    /**
     * @var CurrencyManager
     */
    protected $currencyManager;

    /**
     * @var array 
     */
    private $currencies;

    public function __construct(CurrencyManager $currencyManager)
    {
        $this->currencyManager = $currencyManager;
    }

    public function transform($value, array $options = array())
    {
        $currencies = $this->getCurrencies();

        $result = array();
        foreach (explode(',', $value) as $price) {
            $price = trim($price);
            if (empty($price)) {
                continue;
            }

            if (0 === preg_match('/^([0-9]*\.?[0-9]*) (\w+)$/', $price, $matches)) {
                throw new InvalidValueException('Malformed price: %value%', array('%value%'=>$price));
            }

            if (in_array($matches[2], $currencies)) {
                $result[] = array('data' => $matches[1], 'currency' => $matches[2]);
                unset($currencies[array_search($matches[2], $currencies)]);
            }
        }

        foreach ($currencies as $currency) {
            $result[] = array('data' => '', 'currency' => $currency);
        }

        return $result;
    }

    protected function getCurrencies()
    {
        if (!isset($this->currencies)) {
            $this->currencies = $this->currencyManager->getActiveCodes();
        }

        return $this->currencies;
    }
}
