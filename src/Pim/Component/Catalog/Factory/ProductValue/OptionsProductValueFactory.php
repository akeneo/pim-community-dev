<?php

namespace Pim\Component\Catalog\Factory\ProductValue;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Repository\AttributeOptionRepositoryInterface;

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
    /** @var AttributeOptionRepositoryInterface */
    protected $attrOptionRepository;

    /** @var string */
    protected $productValueClass;

    /** @var string */
    protected $supportedAttributeType;

    /**
     * @param AttributeOptionRepositoryInterface $attrOptionRepository
     * @param string $productValueClass
     * @param $supportedAttributeType
     */
    public function __construct(
        AttributeOptionRepositoryInterface $attrOptionRepository,
        $productValueClass,
        $supportedAttributeType
    ) {
        $this->attrOptionRepository = $attrOptionRepository;
        $this->productValueClass = $productValueClass;
        $this->supportedAttributeType = $supportedAttributeType;
    }

    /**
     * {@inheritdoc}
     */
    public function create(AttributeInterface $attribute, $channelCode, $localeCode, $data)
    {
        $this->checkData($attribute, $data);

        if (null === $data) {
            $data = [];
        }

        $value = new $this->productValueClass(
            $attribute,
            $channelCode,
            $localeCode,
            $this->getOptions($attribute, $data)
        );

        return $value;
    }

    /**
     * {@inheritdoc}
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
        if (null === $data || [] === $data) {
            return;
        }

        if (!is_array($data)) {
            throw InvalidArgumentException::arrayExpected(
                $attribute->getCode(),
                'multi select',
                'factory',
                gettype($data)
            );
        }

        foreach ($data as $key => $value) {
            if (!is_string($value)) {
                throw InvalidArgumentException::arrayStringValueExpected(
                    $attribute->getCode(),
                    $key,
                    'multi select',
                    'factory',
                    gettype($value)
                );
            }
        }
    }

    /**
     * Returns an ArrayCollection of attribute options.
     *
     * @param AttributeInterface $attribute
     * @param string[]           $data
     *
     * @return ArrayCollection
     */
    protected function getOptions(AttributeInterface $attribute, array $data)
    {
        $options = new ArrayCollection();

        foreach ($data as $optionCode) {
            if (null !== $option = $this->getOption($attribute, $optionCode)) {
                $options->add($option);
            }
        }

        return $options;
    }

    /**
     * Gets an attribute option from its code.
     *
     * @param AttributeInterface $attribute
     * @param string             $optionCode
     *
     * @throws InvalidArgumentException
     * @return AttributeOptionInterface|null
     */
    protected function getOption(AttributeInterface $attribute, $optionCode)
    {
        $identifier = $attribute->getCode() . '.' . $optionCode;
        $option = $this->attrOptionRepository->findOneByIdentifier($identifier);

        if (null === $option) {
            throw InvalidArgumentException::arrayInvalidKey(
                $attribute->getCode(),
                'code',
                'The option does not exist',
                'multi select',
                'factory',
                $optionCode
            );
        }

        return $option;
    }
}
