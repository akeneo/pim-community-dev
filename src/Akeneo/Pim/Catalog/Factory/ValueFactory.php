<?php

namespace Pim\Component\Catalog\Factory;

use Pim\Component\Catalog\Exception\InvalidAttributeException;
use Pim\Component\Catalog\Factory\Value\ValueFactoryInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;

/**
 * Factory that creates product values.
 *
 * "RegisterValueFactoryPass" allows to register private product value
 * factories tagged with "pim_catalog.factory.value".
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValueFactory
{
    /** @var AttributeValidatorHelper */
    protected $attributeValidatorHelper;

    /** @var ValueFactoryInterface[] */
    protected $factories;

    /**
     * @param AttributeValidatorHelper $attributeValidatorHelper
     * @param ValueFactoryInterface[]  $factories
     */
    public function __construct(
        AttributeValidatorHelper $attributeValidatorHelper,
        $factories = []
    ) {
        $this->attributeValidatorHelper = $attributeValidatorHelper;
        $this->factories = $factories;
    }

    /**
     * This method effectively creates a product value and set its data, while
     * checking the provided localeCode and ChannelCode exists.
     *
     * @param AttributeInterface $attribute
     * @param string             $channelCode
     * @param string             $localeCode
     * @param mixed              $data
     *
     * @throws \LogicException
     *
     * @return ValueInterface
     */
    public function create(AttributeInterface $attribute, $channelCode, $localeCode, $data)
    {
        try {
            $this->attributeValidatorHelper->validateScope($attribute, $channelCode);
            $this->attributeValidatorHelper->validateLocale($attribute, $localeCode);
        } catch (\LogicException $e) {
            throw InvalidAttributeException::expectedFromPreviousException('attribute', self::class, $e);
        }

        $factory = $this->getFactory($attribute->getType());
        $value = $factory->create($attribute, $channelCode, $localeCode, $data);

        return $value;
    }

    /**
     * @param ValueFactoryInterface $factory
     */
    public function registerFactory(ValueFactoryInterface $factory)
    {
        $this->factories[] = $factory;
    }

    /**
     * @param string $attributeType
     *
     * @return ValueFactoryInterface
     *
     */
    protected function getFactory($attributeType)
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($attributeType)) {
                return $factory;
            }
        }

        throw new \OutOfBoundsException(sprintf(
            'No factory has been registered to create a Product Value for the attribute type "%s"',
            $attributeType
        ));
    }
}
