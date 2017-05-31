<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\Localization\TranslatableUpdater;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Factory\AttributeRequirementFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeRequirementInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\AttributeRequirementRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Updates a family.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyUpdater implements ObjectUpdaterInterface
{
    /** @var PropertyAccessor */
    protected $accessor;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $familyRepository;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var AttributeRequirementFactory */
    protected $attrRequiFactory;

    /** @var AttributeRequirementRepositoryInterface */
    protected $requirementRepo;

    /** @var TranslatableUpdater */
    protected $translatableUpdater;

    /**
     * @param IdentifiableObjectRepositoryInterface   $familyRepository
     * @param AttributeRepositoryInterface            $attributeRepository
     * @param ChannelRepositoryInterface              $channelRepository
     * @param AttributeRequirementFactory             $attrRequiFactory
     * @param AttributeRequirementRepositoryInterface $requirementRepo
     * @param TranslatableUpdater                     $translatableUpdater
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository,
        ChannelRepositoryInterface $channelRepository,
        AttributeRequirementFactory $attrRequiFactory,
        AttributeRequirementRepositoryInterface $requirementRepo,
        TranslatableUpdater $translatableUpdater
    ) {
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->familyRepository = $familyRepository;
        $this->attributeRepository = $attributeRepository;
        $this->channelRepository = $channelRepository;
        $this->attrRequiFactory = $attrRequiFactory;
        $this->requirementRepo = $requirementRepo;
        $this->translatableUpdater = $translatableUpdater;
    }

    /**
     * {@inheritdoc}
     */
    public function update($family, array $data, array $options = [])
    {
        if (!$family instanceof FamilyInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($family),
                FamilyInterface::class
            );
        }

        foreach ($data as $field => $value) {
            $this->validateDataType($field, $value);
            $this->setData($family, $field, $value);
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
        if (in_array($field, ['code', 'attribute_as_label'])) {
            if (null !== $data && !is_scalar($data)) {
                throw InvalidPropertyTypeException::scalarExpected($field, static::class, $data);
            }
        } elseif (in_array($field, ['attributes', 'labels'])) {
            $this->validateScalarArray($field, $data);
        } elseif ('attribute_requirements' === $field) {
            $this->validateAttributeRequirements($data);
        } else {
            throw UnknownPropertyException::unknownProperty($field);
        }
    }

    /**
     * Validate that it is an array with scalar values.
     *
     * @param string $field
     * @param mixed $data
     *
     * @throws InvalidPropertyTypeException
     */
    protected function validateScalarArray($field, $data)
    {
        if (!is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected($field, static::class, $data);
        }

        foreach ($data as $value) {
            if (null !== $value && !is_scalar($value)) {
                throw InvalidPropertyTypeException::validArrayStructureExpected(
                    $field,
                    sprintf('one of the %s is not a scalar', $field),
                    static::class,
                    $data
                );
            }
        }
    }
    /**
     * @param mixed $data
     *
     * @throws InvalidPropertyTypeException
     */
    protected function validateAttributeRequirements($data)
    {
        if (!is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected('attribute_requirements', 'update', 'family', $data);
        }
        foreach ($data as $channel => $attributes) {
            if (!is_array($attributes)) {
                throw InvalidPropertyTypeException::validArrayStructureExpected(
                    'attribute_requirements',
                    sprintf('the channel "%s" is not an array', $channel),
                    static::class,
                    $data
                );
            }
            foreach ($attributes as $attribute) {
                if (null !== $attribute && !is_scalar($attribute)) {
                    throw InvalidPropertyTypeException::validArrayStructureExpected(
                        'attribute_requirements',
                        sprintf('one of the attributes in the channel "%s" is not a scalar', $channel),
                        static::class,
                        $data
                    );
                }
            }
        }
    }

    /**
     * @param FamilyInterface $family
     * @param string          $field
     * @param mixed           $data
     *
     * @throws UnknownPropertyException
     * @throws InvalidPropertyException
     */
    protected function setData(FamilyInterface $family, $field, $data)
    {
        switch ($field) {
            case 'labels':
                $this->translatableUpdater->update($family, $data);
                break;
            case 'attribute_requirements':
                $this->setAttributeRequirements($family, $data);
                break;
            case 'attributes':
                $this->addAttributes($family, $data);
                break;
            case 'attribute_as_label':
                $this->setAttributeAsLabel($family, $data);
                break;
            default:
                $this->setValue($family, $field, $data);
        }
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
     * Set the new attribute requirements.
     * If a channel is not present in the requirement list, this method does not update the requirements of this
     * channel.
     *
     * @param FamilyInterface $family
     * @param array           $newRequirements The requirements for each channel. For example:
     *                                         ['mobile' => ['attr1', 'attr2'], 'tabled' => ['attr3']]
     *
     * @throws InvalidPropertyException
     */
    protected function setAttributeRequirements(FamilyInterface $family, array $newRequirements)
    {
        foreach ($family->getAttributeRequirements() as $requirement) {
            $channelCode = $requirement->getChannelCode();
            if (array_key_exists($channelCode, $newRequirements)) {
                $attribute = $requirement->getAttribute();
                $key = array_search($attribute->getCode(), $newRequirements[$channelCode], true);
                if (false === $key && AttributeTypes::IDENTIFIER !== $attribute->getType()) {
                    $family->removeAttributeRequirement($requirement);
                } elseif (false !== $key) {
                    unset($newRequirements[$channelCode][$key]);
                }
            }
        }

        foreach ($newRequirements as $channelCode => $requirements) {
            $createdRequirements = $this->createAttributeRequirementsByChannel($family, $requirements, $channelCode);
            foreach ($createdRequirements as $createdRequirement) {
                $family->addAttributeRequirement($createdRequirement);
            }
        }
    }

    /**
     * @param FamilyInterface $family
     * @param array           $attributeCodes
     * @param string          $channelCode
     *
     * @throws InvalidPropertyException
     *
     * @return array
     */
    protected function createAttributeRequirementsByChannel(
        FamilyInterface $family,
        array $attributeCodes,
        $channelCode
    ) {
        $requirements = [];

        $channel = $this->channelRepository->findOneByIdentifier($channelCode);
        if (null === $channel) {
            throw InvalidPropertyException::validEntityCodeExpected(
                'attribute_requirements',
                'code',
                'The channel does not exist',
                static::class,
                $channelCode
            );
        }

        foreach ($attributeCodes as $attributeCode) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
            if (null === $attribute) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    'attribute_requirements',
                    'code',
                    'The attribute does not exist',
                    static::class,
                    $attributeCode
                );
            }
            if (AttributeTypes::IDENTIFIER !== $attribute->getType()) {
                $requirements[] = $this->createAttributeRequirement($family, $attribute, $channel);
            }
        }

        return $requirements;
    }

    /**
     * @param FamilyInterface    $family
     * @param AttributeInterface $attribute
     * @param ChannelInterface   $channel
     *
     * @throws InvalidPropertyException
     *
     * @return AttributeRequirementInterface
     */
    protected function createAttributeRequirement(
        FamilyInterface $family,
        AttributeInterface $attribute,
        ChannelInterface $channel
    ) {
        $requirement = $this->requirementRepo->findOneBy(
            ['attribute' => $attribute->getId(), 'channel' => $channel->getId(), 'family' => $family->getId()]
        );

        if (null === $requirement) {
            $requirement = $this->attrRequiFactory->createAttributeRequirement($attribute, $channel, true);
        }

        return $requirement;
    }

    /**
     * @param FamilyInterface $family
     * @param array           $data
     *
     * @throws InvalidPropertyException
     */
    protected function addAttributes(FamilyInterface $family, array $data)
    {
        foreach ($family->getAttributes() as $attribute) {
            if (AttributeTypes::IDENTIFIER !== $attribute->getType()) {
                $family->removeAttribute($attribute);
            }
        }
        foreach ($data as $attributeCode) {
            if (null !== $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode)) {
                $family->addAttribute($attribute);
            } else {
                throw InvalidPropertyException::validEntityCodeExpected(
                    'attributes',
                    'code',
                    'The attribute does not exist',
                    static::class,
                    $attributeCode
                );
            }
        }
    }

    /**
     * @param FamilyInterface $family
     * @param string          $data
     *
     * @throws InvalidPropertyException
     */
    protected function setAttributeAsLabel(FamilyInterface $family, $data)
    {
        if (null !== $attribute = $this->attributeRepository->findOneByIdentifier($data)) {
            $family->setAttributeAsLabel($attribute);
        } else {
            throw InvalidPropertyException::validEntityCodeExpected(
                'attribute_as_label',
                'code',
                'The attribute does not exist',
                static::class,
                $data
            );
        }
    }
}
