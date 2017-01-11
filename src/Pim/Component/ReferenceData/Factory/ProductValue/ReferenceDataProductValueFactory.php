<?php

namespace Pim\Component\ReferenceData\Factory\ProductValue;

use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Factory\ProductValue\ProductValueFactoryInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\ReferenceData\MethodNameGuesser;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;

/**
 * Factory that creates simple-select and multi-select product values.
 *
 * @internal  Please, do not use this class directly. You must use \Pim\Component\Catalog\Factory\ProductValueFactory.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ReferenceDataProductValueFactory implements ProductValueFactoryInterface
{
    /** @var string */
    protected $productValueClass;

    /** @var string */
    protected $supportedAttributeType;

    /**
     * @param string $productValueClass
     * @param string $supportedAttributeType
     */
    public function __construct($productValueClass, $supportedAttributeType)
    {
        if (!class_exists($productValueClass)) {
            throw new \InvalidArgumentException(
                sprintf('The product value class "%s" does not exist.', $productValueClass)
            );
        }

        $this->productValueClass = $productValueClass;
        $this->supportedAttributeType = $supportedAttributeType;
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

        if ($data instanceof ReferenceDataInterface) {
            $setter = $this->getSetterName($value, $attribute);
            $value->$setter($data);
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

    /**
     * @param ProductValueInterface $value
     * @param AttributeInterface    $attribute
     *
     * @return string
     */
    private function getSetterName(
        ProductValueInterface $value,
        AttributeInterface $attribute
    ) {
        $method = MethodNameGuesser::guess('set', $attribute->getReferenceDataName());

        if (!method_exists($value, $method)) {
            throw new \LogicException(
                sprintf('ProductValue method "%s" is not implemented', true)
            );
        }

        return $method;
    }
}
