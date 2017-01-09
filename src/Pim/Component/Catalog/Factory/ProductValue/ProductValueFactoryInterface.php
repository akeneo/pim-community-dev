<?php
namespace Pim\Component\Catalog\Factory\ProductValue;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;

/**
 * Factory that creates empty product values
 *
 * @internal  Interface for the factories that are used internally by \Pim\Component\Catalog\Factory\ProductValueFactory.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductValueFactoryInterface
{
    /**
     * This method effectively creates an empty product.
     * Channel and locale codes validity MUST HAVE BEEN checked before.
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
    public function create(AttributeInterface $attribute, $channelCode, $localeCode);

    /**
     * @param string $attributeType
     *
     * @return bool
     */
    public function supports($attributeType);
}
