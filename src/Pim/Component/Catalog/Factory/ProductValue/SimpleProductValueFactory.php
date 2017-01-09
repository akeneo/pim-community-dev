<?php

namespace Pim\Component\Catalog\Factory\ProductValue;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;

/**
 * Factory that creates simple product values
 *
 * @internal  Please, do not use this class directly. You must use \Pim\Component\Catalog\Factory\ProductValueFactory.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SimpleProductValueFactory implements ProductValueFactoryInterface
{
    /** @var string */
    protected $productValueClass;

    /** @var array */
    protected $supportedAttributeTypes;

    /**
     * @param array  $supportedAttributeTypes
     * @param string $productValueClass
     */
    public function __construct(array $supportedAttributeTypes, $productValueClass)
    {
        $this->supportedAttributeTypes = $supportedAttributeTypes;

        if (!class_exists($productValueClass)) {
            throw new \InvalidArgumentException(
                sprintf('The product value class "%s" does not exist.', $productValueClass)
            );
        }

        $this->productValueClass = $productValueClass;
    }

    /**
     * @inheritdoc
     */
    public function create(AttributeInterface $attribute, $channelCode, $localeCode)
    {
        /** @var ProductValueInterface $value */
        $value = new $this->productValueClass();
        $value->setAttribute($attribute);
        $value->setScope($channelCode);
        $value->setLocale($localeCode);

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function supports($attributeType)
    {
        return in_array($attributeType, $this->supportedAttributeTypes);
    }
}
