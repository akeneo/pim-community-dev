<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\Localization\TranslatableUpdater;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\AttributeTypeRegistry;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Repository\AttributeGroupRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Updates an attribute.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeUpdater implements ObjectUpdaterInterface
{
    /** @var AttributeGroupRepositoryInterface */
    protected $attrGroupRepo;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var AttributeTypeRegistry */
    protected $registry;

    /** @var PropertyAccessor */
    protected $accessor;

    /** @var TranslatableUpdater */
    protected $translatableUpdater;

    /**
     * @param AttributeGroupRepositoryInterface $attrGroupRepo
     * @param LocaleRepositoryInterface         $localeRepository
     * @param AttributeTypeRegistry             $registry
     * @param TranslatableUpdater               $translatableUpdater
     */
    public function __construct(
        AttributeGroupRepositoryInterface $attrGroupRepo,
        LocaleRepositoryInterface $localeRepository,
        AttributeTypeRegistry $registry,
        TranslatableUpdater $translatableUpdater
    ) {
        $this->attrGroupRepo = $attrGroupRepo;
        $this->localeRepository = $localeRepository;
        $this->registry = $registry;
        $this->translatableUpdater = $translatableUpdater;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function update($attribute, array $data, array $options = [])
    {
        if (!$attribute instanceof AttributeInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($attribute),
                AttributeInterface::class
            );
        }

        foreach ($data as $field => $value) {
            $this->validateDataType($field, $value);
            $this->setData($attribute, $field, $value);
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
        if (in_array($field, ['labels', 'available_locales', 'allowed_extensions'])) {
            if (!is_array($data)) {
                throw InvalidPropertyTypeException::arrayExpected($field, static::class, $data);
            }

            foreach ($data as $key => $value) {
                if (null !== $value && !is_scalar($value)) {
                    throw InvalidPropertyTypeException::validArrayStructureExpected(
                        $field,
                        sprintf('one of the "%s" values is not a scalar', $field),
                        static::class,
                        $data
                    );
                }
            }
        } elseif (in_array(
            $field,
            [
                'code',
                'type',
                'group',
                'unique',
                'useable_as_grid_filter',
                'metric_family',
                'default_metric_unit',
                'reference_data_name',
                'max_characters',
                'validation_rule',
                'validation_regexp',
                'wysiwyg_enabled',
                'number_min',
                'number_max',
                'decimals_allowed',
                'negative_allowed',
                'date_min',
                'date_max',
                'max_file_size',
                'minimum_input_length',
                'sort_order',
                'localizable',
                'scopable',
            ]
        )) {
            if (null !== $data && !is_scalar($data)) {
                throw InvalidPropertyTypeException::scalarExpected($field, static::class, $data);
            }
        } else {
            throw UnknownPropertyException::unknownProperty($field);
        }
    }

    /**
     * @param AttributeInterface $attribute
     * @param string             $field
     * @param mixed              $data
     *
     * @throws InvalidPropertyException
     * @throws UnknownPropertyException
     */
    protected function setData(AttributeInterface $attribute, $field, $data)
    {
        switch ($field) {
            case 'type':
                $this->setType($attribute, $data);
                break;
            case 'labels':
                $this->translatableUpdater->update($attribute, $data);
                break;
            case 'group':
                $this->setGroup($attribute, $data);
                break;
            case 'available_locales':
                $this->setAvailableLocales($attribute, $field, $data);
                break;
            case 'date_min':
                $this->validateDateFormat('date_min', $data);
                $date = $this->getDate($data);
                $attribute->setDateMin($date);
                break;
            case 'date_max':
                $this->validateDateFormat('date_max', $data);
                $date = $this->getDate($data);
                $attribute->setDateMax($date);
                break;
            case 'allowed_extensions':
                $attribute->setAllowedExtensions(implode(',', $data));
                break;
            default:
                $this->setValue($attribute, $field, $data);
        }
    }

    /**
     * @param string $code
     *
     * @return AttributeGroupInterface|null
     */
    protected function findAttributeGroup($code)
    {
        $attributeGroup = $this->attrGroupRepo->findOneByIdentifier($code);

        return $attributeGroup;
    }

    /**
     * @param $attribute
     * @param $field
     * @param $data
     *
     * @throws UnknownPropertyException
     */
    protected function setValue($attribute, $field, $data)
    {
        try {
            $this->accessor->setValue($attribute, $field, $data);
        } catch (NoSuchPropertyException $e) {
            throw UnknownPropertyException::unknownProperty($field, $e);
        }
    }

    /**
     * @param AttributeInterface $attribute
     * @param string             $field
     * @param array              $availableLocaleCodes
     *
     * @throws UnknownPropertyException
     * @throws InvalidPropertyException
     */
    protected function setAvailableLocales(AttributeInterface $attribute, $field, array $availableLocaleCodes)
    {
        $locales = [];
        foreach ($availableLocaleCodes as $localeCode) {
            $locale = $this->localeRepository->findOneByIdentifier($localeCode);
            if (null === $locale) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    'available_locales',
                    'locale code',
                    'The locale does not exist',
                    static::class,
                    $localeCode
                );
            }

            $locales[] = $locale;
        }

        $this->setValue($attribute, $field, $locales);
    }

    /**
     * @param AttributeInterface $attribute
     * @param string             $data
     *
     * @throws InvalidPropertyException
     */
    protected function setGroup(AttributeInterface $attribute, $data)
    {
        $attributeGroup = $this->findAttributeGroup($data);
        if (null === $attributeGroup) {
            throw InvalidPropertyException::validEntityCodeExpected(
                'group',
                'code',
                'The attribute group does not exist',
                static::class,
                $data
            );
        }

        $attribute->setGroup($attributeGroup);
    }

    /**
     * @param $attribute
     * @param $data
     *
     * @throws InvalidPropertyException
     */
    protected function setType($attribute, $data)
    {
        if (('' === $data) || (null === $data)) {
            throw InvalidPropertyException::valueNotEmptyExpected('type', static::class);
        }

        try {
            $attributeType = $this->registry->get($data);
            $attribute->setAttributeType($attributeType->getName());
            $attribute->setBackendType($attributeType->getBackendType());
            $attribute->setUnique($attributeType->isUnique());
        } catch (\LogicException $exception) {
            throw InvalidPropertyException::validEntityCodeExpected(
                'type',
                'attribute type',
                'The attribute type does not exist',
                static::class,
                $data
            );
        }
    }

    /**
     * Valid dates:
     * - "2015-12-31T00:00:00+01:00"
     * - "2015-12-31"
     *
     * Wrong dates:
     * - "2015/12/31"
     * - "2015-45-31"
     * - "not a date"
     *
     * @param string $field
     * @param string $data
     *
     * @throws InvalidPropertyException
     */
    protected function validateDateFormat($field, $data)
    {
        if (null === $data) {
            return;
        }

        try {
            new \DateTime($data);
        } catch (\Exception $e) {
            throw InvalidPropertyException::dateExpected($field, 'yyyy-mm-dd', static::class, $data);
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}/', $data)) {
            throw InvalidPropertyException::dateExpected($field, 'yyyy-mm-dd', static::class, $data);
        }
    }

    /**
     * @param string $date
     *
     * @return \DateTime|null
     */
    protected function getDate($date)
    {
        if (null === $date) {
            return null;
        }

        return new \DateTime($date);
    }
}
