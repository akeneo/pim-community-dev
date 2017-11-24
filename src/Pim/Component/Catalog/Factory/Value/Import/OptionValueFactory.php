<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\Factory\Value\Import;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Catalog\Exception\InvalidOptionException;
use Pim\Component\Catalog\Factory\Value\ValueFactoryInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\ValueInterface;

/**
 * Factory that creates option (simple-select) product values.
 *
 * @internal  Please, do not use this class directly. You must use \Pim\Component\Catalog\Factory\ValueFactory.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class OptionValueFactory implements ValueFactoryInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $attrOptionRepository;

    /** @var string */
    protected $productValueClass;

    /** @var string */
    protected $supportedAttributeType;

    /**
     * @param IdentifiableObjectRepositoryInterface $attrOptionRepository
     * @param string                             $productValueClass
     * @param string                             $supportedAttributeType
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
    public function create(AttributeInterface $attribute, $channelCode, $localeCode, $data): ValueInterface
    {
        $this->checkData($attribute, $data);

        if (null !== $data) {
            $data = $this->getOption($attribute, $data);
        }

        $value = new $this->productValueClass($attribute, $channelCode, $localeCode, $data);

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($attributeType): bool
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
    protected function checkData(AttributeInterface $attribute, $data): void
    {
        if (null === $data) {
            return;
        }

        if (!is_string($data) && !is_numeric($data)) {
            throw InvalidPropertyTypeException::stringExpected(
                $attribute->getCode(),
                static::class,
                $data
            );
        }
    }

    /**
     * Gets an attribute option from its code.
     *
     * @param AttributeInterface $attribute
     * @param string|null        $optionCode
     *
     * @throws InvalidOptionException
     * @return AttributeOptionInterface
     */
    protected function getOption(AttributeInterface $attribute, $optionCode): AttributeOptionInterface
    {
        if (null === $optionCode) {
            return null;
        }

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
