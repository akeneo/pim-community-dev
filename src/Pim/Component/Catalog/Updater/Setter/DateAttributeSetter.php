<?php

namespace Pim\Component\Catalog\Updater\Setter;

use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;

/**
 * Sets a date value in many products
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateAttributeSetter extends AbstractAttributeSetter
{
    /**
     * @param \Pim\Component\Catalog\Builder\ProductBuilderInterface  $productBuilder
     * @param AttributeValidatorHelper $attrValidatorHelper
     * @param array                    $supportedTypes
     */
    public function __construct(
        ProductBuilderInterface $productBuilder,
        AttributeValidatorHelper $attrValidatorHelper,
        array $supportedTypes
    ) {
        parent::__construct($productBuilder, $attrValidatorHelper);
        $this->supportedTypes = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     *
     * Expected data input format : "yyyy-mm-ddTH:i:sP" (2016-01-01T00:00:00+01:00)
     */
    public function setAttributeData(
        ProductInterface $product,
        AttributeInterface $attribute,
        $data,
        array $options = []
    ) {
        $options = $this->resolver->resolve($options);
        $this->checkLocaleAndScope($attribute, $options['locale'], $options['scope']);
        $data = $this->formatData($attribute, $data);

        $this->setData($product, $attribute, $data, $options['locale'], $options['scope']);
    }

    /**
     * Format data
     *
     * @param AttributeInterface $attribute
     * @param mixed              $data
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    protected function formatData(AttributeInterface $attribute, $data)
    {
        if ($data instanceof \DateTime) {
            $data = $data->format('Y-m-d');
        } elseif (is_string($data)) {
            $this->validateDateFormat($attribute, $data);
        } elseif (null !== $data && !is_string($data)) {
            throw InvalidArgumentException::expected(
                $attribute->getCode(),
                'datetime or string',
                static::class,
                gettype($data)
            );
        }

        return $data;
    }

    /**
     * @param AttributeInterface $attribute
     * @param string             $data
     *
     * @throws InvalidArgumentException
     */
    protected function validateDateFormat(AttributeInterface $attribute, $data)
    {
        try {
            new \DateTime($data);

            if (!preg_match('/^\d{4}-\d{2}-\d{2}/', $data)) {
                throw new \Exception('Invalid date');
            }
        } catch (\Exception $e) {
            throw InvalidArgumentException::expected(
                $attribute->getCode(),
                'a string with the format yyyy-mm-dd',
                static::class,
                gettype($data)
            );
        }
    }

    /**
     * Set the data into the product value
     *
     * @param ProductInterface   $product
     * @param AttributeInterface $attribute
     * @param string             $data
     * @param string             $locale
     * @param string             $scope
     */
    protected function setData(ProductInterface $product, AttributeInterface $attribute, $data, $locale, $scope)
    {
        $value = $product->getValue($attribute->getCode(), $locale, $scope);
        if (null === $value) {
            $value = $this->productBuilder->addProductValue($product, $attribute, $locale, $scope);
        }

        if (null !== $data) {
            $data = new \DateTime($data);
        }

        $value->setData($data);
    }
}
