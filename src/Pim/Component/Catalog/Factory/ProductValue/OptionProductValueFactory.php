<?php

namespace Pim\Component\Catalog\Factory\ProductValue;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Repository\AttributeOptionRepositoryInterface;

/**
 * Factory that creates option (simple-select) product values.
 *
 * @internal  Please, do not use this class directly. You must use \Pim\Component\Catalog\Factory\ProductValueFactory.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class OptionProductValueFactory implements ProductValueFactoryInterface
{
    /** @var AttributeOptionRepositoryInterface */
    protected $attrOptionRepository;

    /** @var string */
    protected $productValueClass;

    /** @var string */
    protected $supportedAttributeType;

    /**
     * @param AttributeOptionRepositoryInterface $attrOptionRepository
     * @param string                             $productValueClass
     * @param string                             $supportedAttributeType
     */
    public function __construct(
        AttributeOptionRepositoryInterface $attrOptionRepository,
        $productValueClass,
        $supportedAttributeType
    ) {
        if (!class_exists($productValueClass)) {
            throw new \InvalidArgumentException(
                sprintf('The product value class "%s" does not exist.', $productValueClass)
            );
        }

        $this->attrOptionRepository = $attrOptionRepository;
        $this->productValueClass = $productValueClass;
        $this->supportedAttributeType = $supportedAttributeType;
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

        if (null !== $option = $this->getOption($attribute, $data)) {
            $value->setOption($option);
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
     * Checks if data is valid.
     *
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

        if (!is_string($data) && !is_numeric($data)) {
            throw InvalidArgumentException::stringExpected(
                $attribute->getCode(),
                'simple select',
                'factory',
                gettype($data)
            );
        }
    }

    /**
     * Gets an attribute option from its code.
     *
     * @param AttributeInterface $attribute
     * @param string|null        $optionCode
     *
     * @throws InvalidPropertyException
     * @return AttributeOptionInterface|null
     */
    protected function getOption(AttributeInterface $attribute, $optionCode)
    {
        if (null === $optionCode) {
            return null;
        }

        $identifier = $attribute->getCode() . '.' . $optionCode;
        $option = $this->attrOptionRepository->findOneByIdentifier($identifier);

        if (null === $option) {
            throw InvalidPropertyException::validEntityCodeExpected(
                $attribute->getCode(),
                'code',
                'The option does not exist',
                'simple select',
                'factory',
                $optionCode
            );
        }

        return $option;
    }
}
