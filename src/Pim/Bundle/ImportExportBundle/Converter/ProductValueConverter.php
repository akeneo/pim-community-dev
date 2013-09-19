<?php

namespace Pim\Bundle\ImportExportBundle\Converter;

use Doctrine\ORM\EntityManager;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;

/**
 * Convert a basic representation of a value into a complex one bindable on a product form
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueConverter
{
    const SCOPE_KEY = '[scope]';

    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function convert($data)
    {
        $scope = $this->getScope($data);

        $result = array();
        foreach ($data as $key => $value) {
            $attribute = $this->getAttribute($key);
            if ($attribute) {
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
        $result = array();
        foreach (explode(',', $value) as $price) {
            list($data, $currency) = explode(' ', $price);
            $result[] = array(
                'data'     => $data,
                'currency' => $currency,
            );
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
        if (false === strpos($value, ' ')) {
            continue;
        }
        list($data, $unit) = explode(' ', $value);

        return $this->convertValue(
            'metric',
            array(
                'data' => $data,
                'unit' => $unit,
            )
        );
    }

    /**
     * Convert value
     *
     * @param string $value
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

    private function getAttribute($code)
    {
        if ($this->isLocalized($code)) {
            $code = $this->getAttributeCode($code);
        }

        return $this->entityManager
            ->getRepository('PimCatalogBundle:ProductAttribute')
            ->findOneBy(array('code' => $code));
    }

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
