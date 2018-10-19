<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOptionsException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * Factory that creates options (multi-select) product values.
 *
 * @internal  Please, do not use this class directly. You must use \Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory.
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
    public function create(AttributeInterface $attribute, $channelCode, $localeCode, $data, $ignoreUnknownData = false)
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
     * @throws InvalidOptionsException
     * @return array
     */
    protected function getOptions(AttributeInterface $attribute, array $data)
    {
        sort($data);

        $options = [];
        $notFoundOptions = [];

        foreach ($data as $optionCode) {
            $identifier = $attribute->getCode() . '.' . $optionCode;
            $option = $this->attrOptionRepository->findOneByIdentifier($identifier);

            if (null === $option) {
                $notFoundOptions[] = $optionCode;
            } else {
                $options[] = $option;
            }
        }

        if (!empty($notFoundOptions)) {
            throw InvalidOptionsException::validEntityListCodesExpected(
                $attribute->getCode(),
                'codes',
                'The options do not exist',
                static::class,
                $notFoundOptions
            );
        }

        return $options;
    }
}
