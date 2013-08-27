<?php

namespace Pim\Bundle\ImportExportBundle\Converter;

use Doctrine\ORM\EntityManager;
use Pim\Bundle\ProductBundle\Entity\ProductAttribute;

/**
 * Convert a basic representation of a value into a complex one bindable on a product form
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueConverter
{
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    /**
     * array(
     *     'sku'        => 'sku-001',
     *     'name-en_US' => 'car',
     *     'name-fr_FR' => 'voiture,
     * )

     * array(
     *     sku' => array(
     *         'varchar' => 'sku-001',
     *     ),
     *     'name_en_US' => array(
     *         'varchar' => 'car'
     *     ),
     *     'name_fr_FR' => array(
     *         'varchar' => 'voiture'
     *     ),
     * )
     */
    public function convert($data, array $context = array())
    {
        $context = $this->processContext($context);

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

                    default:
                        $value = $this->convertValue($attribute->getBackendType(), $value);
                }
                $key = $this->getProductValueKey($attribute, $key, $context);
                $result[$key] = $value;
            }
        }

        return $result;
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

        return array();
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
     * Define default values within the context
     *
     * @param array $constext
     *
     * @return array
     */
    private function processContext(array $context)
    {
        return array_merge(
            array(
                'scope' => null
            ),
            $context
        );
    }

    private function getAttribute($code)
    {
        if ($this->isLocalized($code)) {
            $code = $this->getAttributeCode($code);
        }

        return $this->entityManager
            ->getRepository('PimProductBundle:ProductAttribute')
            ->findOneBy(array('code' => $code));
    }

    public function getOption($code)
    {
        return $this->entityManager
            ->getRepository('PimProductBundle:AttributeOption')
            ->findOneBy(array('code' => $code));
    }

    /**
     * Get the product value key as the one used by Product::getValues()
     *
     * @param ProductAttribute $attribute
     * @param string           $key
     * @param array            $context
     *
     * @return string
     */
    private function getProductValueKey(ProductAttribute $attribute, $key, array $context)
    {
        $suffix = '';
        if ($attribute->getScopable()) {
            $suffix = sprintf('_%s', $context['scope']);
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
