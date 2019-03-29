<?php

namespace Akeneo\Pim\Structure\Component\Updater;

use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeGroupRepositoryInterface;
use Akeneo\Tool\Component\Localization\TranslatableUpdater;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;

/**
 * Updates an attribute group
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupUpdater implements ObjectUpdaterInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    /** @var AttributeGroupRepositoryInterface */
    protected $attributeGroupRepository;

    /** @var TranslatableUpdater */
    protected $translatableUpdater;

    /**
     * @param IdentifiableObjectRepositoryInterface $attributeRepository
     * @param AttributeGroupRepositoryInterface     $attributeGroupRepository
     * @param TranslatableUpdater                   $translatableUpdater
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        AttributeGroupRepositoryInterface $attributeGroupRepository,
        TranslatableUpdater $translatableUpdater
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->attributeGroupRepository = $attributeGroupRepository;
        $this->translatableUpdater = $translatableUpdater;
    }

    /**
     * {@inheritdoc}
     *
     * Expected input format :
     * [
     *     'code'       => 'sizes',
     *     'sort_order' => 1,
     *     'attributes' => ['size', 'main_color'],
     *     'labels'     => [
     *         'en_US' => 'Sizes',
     *         'fr_FR' => 'Tailles'
     *     ]
     * ]
     */
    public function update($attributeGroup, array $data, array $options = [])
    {
        if (!$attributeGroup instanceof AttributeGroupInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($attributeGroup),
                AttributeGroupInterface::class
            );
        }

        foreach ($data as $field => $value) {
            $this->validateDataType($field, $value);
            $this->setData($attributeGroup, $field, $value);
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
        if (in_array($field, ['labels', 'attributes'])) {
            if (!is_array($data)) {
                throw InvalidPropertyTypeException::arrayExpected($field, static::class, $data);
            }

            foreach ($data as $value) {
                if (null !== $value && !is_scalar($value)) {
                    throw InvalidPropertyTypeException::validArrayStructureExpected(
                        $field,
                        sprintf('one of the "%s" values is not a scalar', $field),
                        static::class,
                        $data
                    );
                }
            }
        } elseif (in_array($field, ['code', 'sort_order'])) {
            if (null !== $data && !is_scalar($data)) {
                throw InvalidPropertyTypeException::scalarExpected($field, static::class, $data);
            }
        } else {
            throw UnknownPropertyException::unknownProperty($field);
        }
    }

    /**
     * @param AttributeGroupInterface $attributeGroup
     * @param string                  $field
     * @param mixed                   $data
     *
     * @throws InvalidPropertyException
     */
    protected function setData($attributeGroup, $field, $data)
    {
        if ('code' == $field) {
            $attributeGroup->setCode($data);
        } elseif ('sort_order' == $field) {
            $attributeGroup->setSortOrder($data);
        } elseif ('attributes' == $field) {
            $this->setAttributes($attributeGroup, $data);
        } elseif ('labels' == $field) {
            $this->translatableUpdater->update($attributeGroup, $data);
        }
    }

    protected function findAttribute(string $attributeCode): ?AttributeInterface
    {
        return $this->attributeRepository->findOneByIdentifier($attributeCode);
    }

    /**
     * @param AttributeGroupInterface $attributeGroup
     * @param string[]                $data
     *
     * @throws InvalidPropertyException
     */
    protected function setAttributes(AttributeGroupInterface $attributeGroup, array $data)
    {
        if (AttributeGroup::DEFAULT_GROUP_CODE === $attributeGroup->getCode()) {
            return;
        }

        $defaultGroup = $this->attributeGroupRepository->findDefaultAttributeGroup();

        foreach ($attributeGroup->getAttributes() as $attribute) {
            if (!in_array($attribute->getCode(), $data)) {
                $defaultGroup->addAttribute($attribute);
            }
        }

        foreach ($data as $attributeCode) {
            $attribute = $this->findAttribute($attributeCode);
            if (null === $attribute) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    'attributes',
                    'attribute code',
                    'The attribute does not exist',
                    static::class,
                    $attributeCode
                );
            }
            $attributeGroup->addAttribute($attribute);
        }
    }
}
