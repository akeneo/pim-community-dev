<?php

namespace Akeneo\Pim\Structure\Component\Updater;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;

/**
 * Updates and validates an attribute option
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionUpdater implements ObjectUpdaterInterface
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     *
     * Expected input format :
     * {
     *     'attribute': 'maximum_print_size',
     *     'code': '210_x_1219_mm',
     *     'sort_order': 2,
     *     'labels': {
     *         'de_DE': '210 x 1219 mm',
     *         'en_US': '210 x 1219 mm',
     *         'fr_FR': '210 x 1219 mm'
     *     }
     * }
     */
    public function update($attributeOption, array $data, array $options = [])
    {
        if (!$attributeOption instanceof AttributeOptionInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($attributeOption),
                AttributeOptionInterface::class
            );
        }

        foreach ($data as $field => $value) {
            $this->validateDataType($field, $value);
            $this->setData($attributeOption, $field, $value);
        }

        return $this;
    }

    /**
     * Validate the data type of a field.
     *
     * @param string $field
     * @param mixed  $data
     *
     * @throws InvalidPropertyTypeException
     * @throws UnknownPropertyException
     */
    protected function validateDataType($field, $data)
    {
        if ('labels' === $field) {
            if (!is_array($data)) {
                throw InvalidPropertyTypeException::arrayExpected($field, static::class, $data);
            }

            foreach ($data as $localeCode => $label) {
                if (null !== $label && !is_scalar($label)) {
                    throw InvalidPropertyTypeException::validArrayStructureExpected(
                        'labels',
                        'one of the labels is not a scalar',
                        static::class,
                        $data
                    );
                }
            }
        } elseif (in_array($field, ['attribute', 'code', 'sort_order'])) {
            if (null !== $data && !is_scalar($data)) {
                throw InvalidPropertyTypeException::scalarExpected($field, static::class, $data);
            }
        } else {
            throw UnknownPropertyException::unknownProperty($field);
        }
    }

    /**
     * @param AttributeOptionInterface $attributeOption
     * @param string                   $field
     * @param mixed                    $data
     *
     * @throws InvalidPropertyException
     */
    protected function setData(AttributeOptionInterface $attributeOption, $field, $data)
    {
        if ('code' === $field && $attributeOption->getId() === null) {
            $attributeOption->setCode($data);
        }

        if ('attribute' === $field) {
            $attribute = $this->findAttribute($data);
            if (null !== $attribute) {
                $attributeOption->setAttribute($attribute);
            } else {
                throw InvalidPropertyException::validEntityCodeExpected(
                    'attribute',
                    'attribute code',
                    'The attribute does not exist',
                    static::class,
                    $data
                );
            }
        }

        if ('labels' === $field) {
            foreach ($data as $localeCode => $label) {
                $attributeOption->setLocale($localeCode);
                $translation = $attributeOption->getTranslation();

                if (null === $label || '' === $label) {
                    $attributeOption->removeOptionValue($translation);
                } else {
                    $translation->setLabel($label);
                }
            }
        }

        if ('sort_order' === $field) {
            $attributeOption->setSortOrder($data);
        }
    }

    /**
     * @param string $code
     *
     * @return AttributeInterface|null
     */
    protected function findAttribute($code)
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($code);

        return $attribute;
    }
}
