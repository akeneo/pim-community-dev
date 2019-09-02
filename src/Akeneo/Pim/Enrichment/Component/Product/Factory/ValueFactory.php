<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Write\Value\ValueFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

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
     * @param bool               $ignoreUnknownData
     *
     * @throws \LogicException
     *
     * @return ValueInterface
     */
    public function create(
        AttributeInterface $attribute,
        ?string $channelCode,
        ?string $localeCode,
        $data,
        bool $ignoreUnknownData = false
    ) {
        if (null === $data || [] === $data || [''] === $data || [null] === $data) {
            throw new \Exception(sprintf('Data should not be empty, %s found', json_encode($data)));
        }

        return $this->createValue($attribute, $channelCode, $localeCode, $data, $ignoreUnknownData);
    }

    public function createNull(AttributeInterface $attribute, ?string $channelCode, ?string $localeCode)
    {
        return $this->createValue($attribute, $channelCode, $localeCode, null, false);
    }

    private function createValue(
        AttributeInterface $attribute,
        ?string $channelCode,
        ?string $localeCode,
        $data,
        $ignoreUnknownData
    ) {
        try {
            $this->attributeValidatorHelper->validateScope($attribute, $channelCode);
            $this->attributeValidatorHelper->validateLocale($attribute, $localeCode);
        } catch (\LogicException $e) {
            throw InvalidAttributeException::expectedFromPreviousException('attribute', self::class, $e);
        }

        $factory = $this->getFactory($attribute->getType());
        $value = $factory->create($attribute, $channelCode, $localeCode, $data, $ignoreUnknownData);

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
