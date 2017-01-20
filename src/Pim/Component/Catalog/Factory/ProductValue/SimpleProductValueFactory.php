<?php

namespace Pim\Component\Catalog\Factory\ProductValue;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;

/**
 * Factory that creates simple product values (text, textarea and number).
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

    /** @var string */
    protected $supportedAttributeTypes;

    /**
     * @param string $productValueClass
     * @param string $supportedAttributeTypes
     */
    public function __construct($productValueClass, $supportedAttributeTypes)
    {
        if (!class_exists($productValueClass)) {
            throw new \InvalidArgumentException(
                sprintf('The product value class "%s" does not exist.', $productValueClass)
            );
        }

        $this->productValueClass = $productValueClass;
        $this->supportedAttributeTypes = $supportedAttributeTypes;
    }

    /**
     * @inheritdoc
     */
    public function create(AttributeInterface $attribute, $channelCode, $localeCode, $data)
    {
        $this->checkData($attribute, $data);

        $value = new $this->productValueClass();
        $value->setAttribute($attribute);
        $value->setScope($channelCode);
        $value->setLocale($localeCode);

        if (null !== $data) {
            $value->setData($this->convertData($attribute, $data));
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function supports($attributeType)
    {
        return $attributeType === $this->supportedAttributeTypes;
    }

    /**
     * @param AttributeInterface $attribute
     * @param mixed              $data
     *
     * @throws InvalidArgumentException
     */
    protected function checkData(AttributeInterface $attribute, $data)
    {
        if (null === $data) {
            return;
        }

        if (!is_scalar($data)) {
            throw InvalidArgumentException::expected(
                $attribute->getCode(),
                'a scalar',
                'simple',
                'factory',
                gettype($data)
            );
        }
    }

    /**
     * @param AttributeInterface $attribute
     * @param mixed              $data
     *
     * @return mixed
     */
    protected function convertData(AttributeInterface $attribute, $data)
    {
        if (is_string($data) && '' === trim($data)) {
            $data = null;
        }

        if (AttributeTypes::BOOLEAN === $attribute->getAttributeType() &&
            (1 === $data || '1' === $data || 0 === $data || '0' === $data)
        ) {
            $data = boolval($data);
        }

        return $data;
    }
}
