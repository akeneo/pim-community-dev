<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeOptionRepositoryInterface;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;

/**
 * Sets a simple select value in many products
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SimpleSelectValueSetter extends AbstractValueSetter
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
     */
    public function setValue(array $products, AttributeInterface $attribute, $data, $locale = null, $scope = null)
    {
        $this->checkLocaleAndScope($attribute, $locale, $scope, 'simple select');
        $this->checkData($attribute, $data);

        if (null === $data) {
            $option = null;
        } else {
            $option = $this->attrOptionRepository
                ->findOneBy(['code' => $data, 'attribute' => $attribute]);

            if (null === $option) {
                throw InvalidArgumentException::arrayInvalidKey(
                    $attribute->getCode(),
                    'code',
                    'The option does not exist',
                    'setter',
                    'simple select',
                    $data
                );
            }
        }

        foreach ($products as $product) {
            $this->setOption($attribute, $product, $option, $locale, $scope);
        }
    }

    /**
     * Check if data is valid
     *
     * @param AttributeInterface $attribute
     * @param mixed              $data
     */
    protected function checkData(AttributeInterface $attribute, $data)
    {
        if (null === $data) {
            return;
        }

        if (!is_string($data)) {
            throw InvalidArgumentException::stringExpected(
                $attribute->getCode(),
                'setter',
                'simple select',
                gettype($data)
            );
        }
    }

    /**
     * Set option into the product value
     *
     * @param AttributeInterface            $attribute
     * @param ProductInterface              $product
     * @param AttributeOptionInterface|null $option
     * @param string|null                   $locale
     * @param string|null                   $scope
     */
    protected function setOption(
        AttributeInterface $attribute,
        ProductInterface $product,
        AttributeOptionInterface $option = null,
        $locale = null,
        $scope = null
    ) {
        $value = $product->getValue($attribute->getCode(), $locale, $scope);
        if (null === $value) {
            $value = $this->productBuilder->addProductValue($product, $attribute, $locale, $scope);
        }
        $value->setOption($option);
    }
}
