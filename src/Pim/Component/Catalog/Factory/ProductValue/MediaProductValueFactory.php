<?php

namespace Pim\Component\Catalog\Factory\ProductValue;

use Pim\Component\Catalog\Model\AttributeInterface;

/**
 * Factory that creates media product values.
 *
 * @internal  Please, do not use this class directly. You must use \Pim\Component\Catalog\Factory\ProductValueFactory.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class MediaProductValueFactory implements ProductValueFactoryInterface
{
    /** @var string */
    protected $mediaProductValueClass;

    /** @var string */
    protected $supportedAttributeType;

    /**
     * @param string $mediaProductValueClass
     * @param string $supportedAttributeType
     */
    public function __construct($mediaProductValueClass, $supportedAttributeType)
    {
        if (!class_exists($mediaProductValueClass)) {
            throw new \InvalidArgumentException(
                sprintf('The product value class "%s" does not exist.', $mediaProductValueClass)
            );
        }

        $this->mediaProductValueClass = $mediaProductValueClass;
        $this->supportedAttributeType = $supportedAttributeType;
    }

    /**
     * @inheritdoc
     */
    public function create(AttributeInterface $attribute, $channelCode, $localeCode, $data)
    {
        $value = new $this->mediaProductValueClass();
        $value->setAttribute($attribute);
        $value->setScope($channelCode);
        $value->setLocale($localeCode);
        $value->setMedia($data);

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
