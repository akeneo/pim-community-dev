<?php

namespace Pim\Bundle\ImportExportBundle\Converter;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
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
                    case 'media':
                        $value = $this->convertMediaValue($value);
                        break;
                    default:
                        $value = $this->convertValue($attribute->getBackendType(), $value);
                }
                $result[$this->getProductValueKey($attribute, $key)] = $value;
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
    protected function convertPricesValue($value)
    {
        $currencies = $this->currencyManager->getActiveCodes();

        $result = array();
        foreach (explode(',', $value) as $price) {
            $price = trim($price);
            if (empty($price)) {
                continue;
            }

            if (0 === preg_match('/^([0-9]*\.?[0-9]*) (\w+)$/', $price, $matches)) {
                throw new \InvalidArgumentException(sprintf('Malformed price: %s', $price));
            }

            if (in_array($matches[2], $currencies)) {
                $result[] = array('data' => $matches[1], 'currency' => $matches[2]);
                unset($currencies[array_search($matches[2], $currencies)]);
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
    protected function convertDateValue($value)
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
    protected function convertOptionValue($value)
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
    protected function convertOptionsValue($value)
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
    protected function convertMetricValue($value)
    {
        if (empty($value)) {
            $metric = array();
        } else {
            if (false === strpos($value, ' ')) {
                throw new \InvalidArgumentException(
                    sprintf('Malformed metric: %s', $value)
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
     * Convert media value
     *
     * @param string $value
     */
    protected function convertMediaValue($value)
    {
        try {
            $file = new File($value);
        } catch (FileNotFoundException $e) {
            $file = null;
        }

        return $this->convertValue('media', array('file' => $file));
    }

    /**
     * Convert value
     *
     * @param string $type
     * @param mixed  $value
     *
     * @return array
     */
    protected function convertValue($type, $value)
    {
        return array($type => $value);
    }

    /**
     * Get ProductAttribute entity by code
     *
     * @param string $code
     *
     * @return ProductAttribute
     */
    protected function getAttribute($code)
    {
        $code = $this->getAttributeCode($code);

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
    protected function getOption($code)
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
     *
     * @return string
     */
    protected function getProductValueKey(ProductAttribute $attribute, $key)
    {
        $code = $key;
        $suffix = '';

        if (strpos($key, '-')) {
            $tokens = explode('-', $key);
            $code = $tokens[0];
            if ($attribute->getScopable() && $attribute->getTranslatable()) {
                if (count($tokens) < 3) {
                    throw new \Exception(
                        sprintf(
                            'The column "%s" must contains attribute, locale and scope codes',
                            $key
                        )
                    );
                }
                $suffix = sprintf('_%s_%s', $tokens[1], $tokens[2]);
            } else {
                if (count($tokens) < 2) {
                    throw new \Exception(
                        sprintf(
                            'The column "%s" must contains attribute and %s code',
                            $key,
                            ($attribute->getScopable()) ? 'scope' : 'locale'
                        )
                    );
                }
                $suffix = sprintf('_%s', $tokens[1]);
            }
        }

        return $code.$suffix;
    }

    /**
     * Return the code part of a localised and / or scoped attribute code
     *
     * @param string $code
     *
     * @return string
     */
    protected function getAttributeCode($code)
    {
        $parts = explode('-', $code);

        return $parts[0];
    }
}
