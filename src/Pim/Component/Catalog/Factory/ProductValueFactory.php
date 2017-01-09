<?php

namespace Pim\Component\Catalog\Factory;

use Pim\Component\Catalog\Factory\ProductValue\ProductValueFactoryRegistry;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;

/**
 * Factory that creates empty product values
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueFactory
{
    /** @var AttributeValidatorHelper */
    protected $attributeValidatorHelper;

    /** @var ProductValueFactoryRegistry */
    protected $registry;

    /**
     * @param AttributeValidatorHelper    $attributeValidatorHelper
     * @param ProductValueFactoryRegistry $registry
     */
    public function __construct(
        AttributeValidatorHelper $attributeValidatorHelper,
        ProductValueFactoryRegistry $registry
    ) {

        $this->registry = $registry;
        $this->attributeValidatorHelper = $attributeValidatorHelper;
    }

    /**
     * This method effectively creates an empty product value while checking the provided localeCode and ChannelCode
     * exists.
     * The Data for this product value should be set in a second time using ProductValue::setData method.
     *
     * @param AttributeInterface $attribute
     * @param string             $channelCode
     * @param string             $localeCode
     *
     * @throws \LogicException
     *
     * @return ProductValueInterface
     */
    public function create(AttributeInterface $attribute, $channelCode, $localeCode)
    {
        $this->attributeValidatorHelper->validateScope($attribute, $channelCode);
        $this->attributeValidatorHelper->validateLocale($attribute, $localeCode);

        $factory = $this->registry->get($attribute->getAttributeType());
        $value = $factory->create($attribute, $channelCode, $localeCode);

        return $value;
    }
}
