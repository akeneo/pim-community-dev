<?php

namespace Pim\Component\Catalog\Factory\Value;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Catalog\Exception\InvalidOptionException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;

/**
 * Factory that creates options (multi-select) product values.
 *
 * @internal  Please, do not use this class directly. You must use \Pim\Component\Catalog\Factory\ValueFactory.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class OptionsValueFactory implements ValueFactoryInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $attrOptionRepository;

    /** @var string */
    protected $productValueClass;

    /** @var string */
    protected $supportedAttributeType;

    /**
     * @param IdentifiableObjectRepositoryInterface $attrOptionRepository
     * @param string $productValueClass
     * @param $supportedAttributeType
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $attrOptionRepository,
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
     * @throws InvalidPropertyTypeException
     */
    protected function checkData(AttributeInterface $attribute, $data)
    {
        if (null === $data || [] === $data) {
            return;
        }

        if (!is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected(
                $attribute->getCode(),
                static::class,
                $data
            );
        }

        foreach ($data as $value) {
            if (!is_string($value)) {
                throw InvalidPropertyTypeException::validArrayStructureExpected(
                    $attribute->getCode(),
                    sprintf('one of the options is not a string, "%s" given', gettype($value)),
                    static::class,
                    $data
                );
            }
        }
    }

    /**
     * Returns an array of attribute options.
     *
     * @param AttributeInterface $attribute
     * @param string[]           $data
     *
     * @return array
     */
    protected function getOptions(AttributeInterface $attribute, array $data)
    {
        $options = [];

        foreach ($data as $optionCode) {
            if (null !== $option = $this->getOption($attribute, $optionCode)) {
                $options[] = $option;
            }
        }

        return $options;
    }

    /**
     * Gets an attribute option from its code.
     *
     * @todo TIP-684: When deleting one element of the collection, we will end up throwing the exception.
     *       Problem is, when loading a product value from single storage, it will be skipped because of
     *       one option, when the others in the collection could be valid. So the value will not be loaded
     *       at all, when what we want is the value to be loaded minus the wrong option.
     *
     * @param AttributeInterface $attribute
     * @param string             $optionCode
     *
     * @throws InvalidOptionException
     * @return AttributeOptionInterface|null
     */
    protected function getOption(AttributeInterface $attribute, $optionCode)
    {
        $identifier = $attribute->getCode() . '.' . $optionCode;
        $option = $this->attrOptionRepository->findOneByIdentifier($identifier);

        if (null === $option) {
            throw InvalidOptionException::validEntityCodeExpected(
                $attribute->getCode(),
                'code',
                'The option does not exist',
                static::class,
                $optionCode
            );
        }

        return $option;
    }
}
