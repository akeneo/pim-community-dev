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

                    default:
                        $value = array($attribute->getBackendType() => $value);
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
            $result['prices'][] = array(
                'data'     => $data,
                'currency' => $currency,
            );
        }

        return $result;
    }

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
