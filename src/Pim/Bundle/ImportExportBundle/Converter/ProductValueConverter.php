<?php

namespace Pim\Bundle\ImportExportBundle\Converter;

use Doctrine\ORM\EntityManager;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;

/**
 * Convert a basic representation of a value into a complex one bindable on a product form
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueConverter
{
    const SCOPE_KEY = '[scope]';

    /**
     * @var EntityManager $entityManager
     */
    protected $entityManager;

    /**
     * @var CurrencyManager $currencyManager
     */
    protected $currencyManager;

    /**
     * Constructor
     *
     * @param EntityManager   $entityManager
     * @param CurrencyManager $currencyManager
     */
    public function __construct(EntityManager $entityManager, CurrencyManager $currencyManager)
    {
        $this->entityManager   = $entityManager;
        $this->currencyManager = $currencyManager;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function convert($data)
    {
        $scope = $this->getScope($data);

        $result = array();
        foreach ($data as $key => $value) {
            $attribute = $this->getAttribute($key);

            // TODO Handle media import
            if ($attribute && 'media' !== $attribute->getBackendType()) {
                switch ($attribute->getBackendType()) {
                    case 'prices':
                        $value = $this->convertPricesValue($value);
                        break;
                    case 'date':
                        $value = $this->convertDateValue($value);
                        break;
                    case 'option':
                        $value = $this->convertOptionValue($value);
                        break;
                    case 'options':
                        $value = $this->convertOptionsValue($value);
                        break;
                    case 'metric':
                        $value = $this->convertMetricValue($value);
                        break;
                    default:
                        $value = $this->convertValue($attribute->getBackendType(), $value);
                }
                $key = $this->getProductValueKey($attribute, $key, $scope);
                $result[$key] = $value;
            }
        }

        if (0 === count($result)) {
            return array();
        }

        return array('values' => $result);
    }

    /**
     * Convert prices values
     *
     * @param string $value
     *
     * @return array
     */
    private function convertPricesValue($value)
    {
        $currencies = $this->currencyManager->getActiveCodes();

        $result = array();
        foreach (explode(',', $value) as $price) {
            $price = trim($price);
            if (empty($price) || false === strpos($price, ' ')) {
                continue;
            }

            list($data, $currency) = explode(' ', $price);
            if (in_array($currency, $currencies)) {
                $result[] = array('data' => $data, 'currency' => $currency);
                unset($currencies[array_search($currency, $currencies)]);
            }
        }

        foreach ($currencies as $currency) {
            $result[] = array('data' => '', 'currency' => $currency);
        }

        return $this->convertValue('prices', $result);
    }

    /**
     * Convert date value
     *
     * @param string $value
     *
     * @return array
     */
    private function convertDateValue($value)
    {
        $date = new \DateTime($value);

        return $this->convertValue('date', $date->format('m/d/Y'));
    }

    /**
     * Convert option value
     *
     * @param string $value
     *
     * @return array
     */
    private function convertOptionValue($value)
    {
        if ($option = $this->getOption($value)) {
            return $this->convertValue('option', $option->getId());
        }
    }

    /**
     * Convert options value
     *
     * @param string $value
     *
     * @return array
     */
    private function convertOptionsValue($value)
    {
        $options = array();
        foreach (explode(',', $value) as $val) {
            if ($option = $this->getOption($val)) {
                $options[] = $option->getId();
            }
        }

        return $this->convertValue('options', $options);
    }

    /**
     * Convert metric value
     *
     * @param string $value
     *
     * @return array
     */
    public function convertMetricValue($value)
    {
        if (empty($value)) {
            $metric = array();
        } else {
            if (false === strpos($value, ' ')) {
                throw new \InvalidArgumentException(
                    sprintf('Metric value "%s" is malformed, must match "<data> <unit>"', $value)
                );
            }
            list($data, $unit) = explode(' ', $value);
            $metric = array(
                'data' => $data,
                'unit' => $unit,
            );
        }

        return $this->convertValue('metric', $metric);
    }

    /**
     * Convert value
     *
     * @param string $type
     * @param mixed  $value
     *
     * @return array
     */
    private function convertValue($type, $value)
    {
        return array($type => $value);
    }

    /**
     * Get the value of the self::SCOPE_KEY
     *
     * @param array $data
     *
     * @return string
     */
    private function getScope(array &$data)
    {
        if (array_key_exists(self::SCOPE_KEY, $data)) {
            $scope = $data[self::SCOPE_KEY];
            unset($data[self::SCOPE_KEY]);

            return $scope;
        }
    }

    /**
     * Get ProductAttribute entity by code
     *
     * @param string $code
     *
     * @return ProductAttribute
     */
    private function getAttribute($code)
    {
        if ($this->isLocalized($code)) {
            $code = $this->getAttributeCode($code);
        }

        return $this->entityManager
            ->getRepository('PimCatalogBundle:ProductAttribute')
            ->findOneBy(array('code' => $code));
    }

    /**
     * Get AttributeOption entity by code
     *
     * @param string $code
     *
     * @return AttributeOption
     */
    private function getOption($code)
    {
        return $this->entityManager
            ->getRepository('PimCatalogBundle:AttributeOption')
            ->findOneBy(array('code' => $code));
    }

    /**
     * Get the product value key as the one used by Product::getValues()
     *
     * @param ProductAttribute $attribute
     * @param string           $key
     * @param strint           $scope
     *
     * @return string
     */
    private function getProductValueKey(ProductAttribute $attribute, $key, $scope)
    {
        $suffix = '';
        if ($attribute->getScopable()) {
            $suffix = sprintf('_%s', $scope);
        }

        if ($this->isLocalized($key) && !$attribute->getTranslatable()) {
            $key = substr($key, 0, strpos($key, '-'));
        }

        return str_replace('-', '_', $key).$suffix;
    }

    /**
     * Wether or not the code is localised
     *
     * @param string $code
     *
     * @return boolean
     */
    private function isLocalized($code)
    {
        return false !== strpos($code, '-');
    }

    /**
     * Return the code part of a localised attribute code
     *
     * @param string $code
     *
     * @return string
     */
    private function getAttributeCode($code)
    {
        $parts = explode('-', $code);

        return $parts[0];
    }
}
