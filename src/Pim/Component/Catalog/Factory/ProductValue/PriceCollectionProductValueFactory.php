<?php

namespace Pim\Component\Catalog\Factory\ProductValue;

use Pim\Component\Catalog\Factory\PriceFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;

/**
 * Factory that creates price collection product values.
 *
 * @internal  Please, do not use this class directly. You must use \Pim\Component\Catalog\Factory\ProductValueFactory.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PriceCollectionProductValueFactory implements ProductValueFactoryInterface
{
    /** @var string */
    protected $productValueClass;

    /** @var string */
    protected $supportedAttributeType;

    /** @var PriceFactory */
    protected $priceFactory;

    /**
     * @param string       $productValueClass
     * @param string       $supportedAttributeType
     * @param PriceFactory $priceFactory
     */
    public function __construct($productValueClass, $supportedAttributeType, PriceFactory $priceFactory)
    {
        if (!class_exists($productValueClass)) {
            throw new \InvalidArgumentException(
                sprintf('The product value class "%s" does not exist.', $productValueClass)
            );
        }

        $this->productValueClass = $productValueClass;
        $this->supportedAttributeType = $supportedAttributeType;
        $this->priceFactory = $priceFactory;
    }

    /**
     * @inheritdoc
     */
    public function create(AttributeInterface $attribute, $channelCode, $localeCode, $data)
    {
        $value = new $this->productValueClass();
        $value->setAttribute($attribute);
        $value->setScope($channelCode);
        $value->setLocale($localeCode);

        if (is_array($data) && !empty($data)) {
            foreach ($data as $price) {
                $value->addPrice($this->priceFactory->createPrice($price['amount'], $price['currency']));
            }
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function supports($attributeType)
    {
        return $attributeType === $this->supportedAttributeType;
    }
}
