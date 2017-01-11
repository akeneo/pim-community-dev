<?php

namespace Pim\Component\Catalog\Factory\ProductValue;

use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;

/**
 * Factory that creates options (multi-select) product values.
 *
 * @internal  Please, do not use this class directly. You must use \Pim\Component\Catalog\Factory\ProductValueFactory.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class OptionsProductValueFactory implements ProductValueFactoryInterface
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

        if ((is_array($data) && !empty($data)) || ($data instanceof Collection && !$data->isEmpty())) {
            foreach ($data as $option) {
                $value->addOption($option);
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
