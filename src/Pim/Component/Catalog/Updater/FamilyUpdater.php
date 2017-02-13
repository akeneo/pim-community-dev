<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Factory\AttributeRequirementFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeRequirementInterface;
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

    /**
     * @param IdentifiableObjectRepositoryInterface   $familyRepository
     * @param AttributeRepositoryInterface            $attributeRepository
     * @param ChannelRepositoryInterface              $channelRepository
     * @param AttributeRequirementFactory             $attrRequiFactory
     * @param AttributeRequirementRepositoryInterface $requirementRepo
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository,
        ChannelRepositoryInterface $channelRepository,
        AttributeRequirementFactory $attrRequiFactory,
        AttributeRequirementRepositoryInterface $requirementRepo
    ) {
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->familyRepository = $familyRepository;
        $this->attributeRepository = $attributeRepository;
        $this->channelRepository = $channelRepository;
        $this->attrRequiFactory = $attrRequiFactory;
        $this->requirementRepo = $requirementRepo;
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
            $this->setData($family, $field, $value);
        }

        return $this;
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
                $this->setLabels($family, $data);
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
     * @param FamilyInterface $family
     * @param array           $data
     */
    protected function setLabels(FamilyInterface $family, array $data)
    {
        foreach ($data as $localeCode => $label) {
            $family->setLocale($localeCode);
            $translation = $family->getTranslation();
            $translation->setLabel($label);
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
                if (false === $key && AttributeTypes::IDENTIFIER !== $attribute->getAttributeType()) {
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
            if (AttributeTypes::IDENTIFIER !== $attribute->getAttributeType()) {
                $requirements[] = $this->createAttributeRequirement($family, $attribute, $channelCode);
            }
        }

        return $requirements;
    }

    /**
     * @param FamilyInterface    $family
     * @param AttributeInterface $attribute
     * @param string             $channelCode
     *
     * @throws InvalidPropertyException
     *
     * @return AttributeRequirementInterface
     */
    protected function createAttributeRequirement(FamilyInterface $family, AttributeInterface $attribute, $channelCode)
    {
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
            if (AttributeTypes::IDENTIFIER !== $attribute->getAttributeType()) {
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
                'attributes',
                'code',
                'The attribute does not exist',
                static::class,
                $data
            );
        }
    }
}
