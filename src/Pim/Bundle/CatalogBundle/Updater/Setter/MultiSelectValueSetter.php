<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Repository\AttributeOptionRepositoryInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;

/**
 * Sets a multi select value in many products
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MultiSelectValueSetter extends AbstractValueSetter
{
    /** @var AttributeOptionRepositoryInterface */
    protected $attrOptionRepository;

    /**
     * @param ProductBuilderInterface            $productBuilder
     * @param AttributeValidatorHelper           $attrValidatorHelper
     * @param AttributeOptionRepositoryInterface $attrOptionRepository
     * @param array                              $supportedTypes
     */
    public function __construct(
        ProductBuilderInterface $productBuilder,
        AttributeValidatorHelper $attrValidatorHelper,
        AttributeOptionRepositoryInterface $attrOptionRepository,
        array $supportedTypes
    ) {
        parent::__construct($productBuilder, $attrValidatorHelper);
        $this->attrOptionRepository = $attrOptionRepository;
        $this->supportedTypes       = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated will be removed in 1.5, use method setAttributeData
     */
    public function setValue(array $products, AttributeInterface $attribute, $data, $locale = null, $scope = null)
    {
        foreach ($products as $product) {
            $this->setAttributeData($product, $attribute, $data, ['locale' => $locale, 'scope' => $scope]);
        }
    }

    /**
     * {@inheritdoc}
     *
     * Expected data input format: ["option_code", "other_option_code"]
     */
    public function setAttributeData(
        ProductInterface $product,
        AttributeInterface $attribute,
        $data,
        array $options = []
    ) {
        $this->resolver->resolve($options);
        $this->checkLocaleAndScope($attribute, $options['locale'], $options['scope'], 'multi select');
        $this->checkData($attribute, $data);

        $attributeOptions = [];
        foreach ($data as $optionCode) {
            $option = $this->attrOptionRepository
                ->findOneBy(['code' => $optionCode, 'attribute' => $attribute]);
            if (null === $option) {
                throw InvalidArgumentException::arrayInvalidKey(
                    $attribute->getCode(),
                    'code',
                    'The option does not exist',
                    'setter',
                    'multi select',
                    $optionCode
                );
            }

            $attributeOptions[] = $option;
        }

        $this->setOptions($product, $attribute, $attributeOptions, $options['locale'], $options['scope']);
    }

    /**
     * Check if data is valid
     *
     * @param AttributeInterface $attribute
     * @param mixed              $data
     */
    protected function checkData(AttributeInterface $attribute, $data)
    {
        if (!is_array($data)) {
            throw InvalidArgumentException::arrayExpected(
                $attribute->getCode(),
                'setter',
                'multi select',
                gettype($data)
            );
        }

        foreach ($data as $key => $value) {
            if (!is_string($value)) {
                throw InvalidArgumentException::arrayStringKeyExpected(
                    $attribute->getCode(),
                    $key,
                    'setter',
                    'multi select',
                    gettype($value)
                );
            }
        }
    }

    /**
     * Set options into the product value
     *
     * @param ProductInterface   $product
     * @param AttributeInterface $attribute
     * @param array              $attributeOptions
     * @param string             $locale
     * @param string             $scope
     */
    protected function setOptions(
        ProductInterface $product,
        AttributeInterface $attribute,
        $attributeOptions,
        $locale,
        $scope
    ) {
        $value = $product->getValue($attribute->getCode(), $locale, $scope);
        if (null === $value) {
            $value = $this->productBuilder->addProductValue($product, $attribute, $locale, $scope);
        }

        foreach ($value->getOptions() as $option) {
            $value->removeOption($option);
        }

        foreach ($attributeOptions as $attributeOption) {
            $value->addOption($attributeOption);
        }
    }
}
