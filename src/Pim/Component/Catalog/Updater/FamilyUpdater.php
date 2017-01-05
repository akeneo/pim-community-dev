<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Collections\ArrayCollection;
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
                'Pim\Component\Catalog\Model\FamilyInterface'
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
     * @param FamilyInterface $family
     * @param array           $data
     *
     * @throws InvalidPropertyException
     */
    protected function setAttributeRequirements(FamilyInterface $family, array $data)
    {
        $oldRequirements = $family->getAttributeRequirements();

        $requirements = $this->getExistingIdentifierRequirements($family);
        foreach ($data as $channelCode => $attributeCodes) {
            $requirements = array_merge(
                $requirements,
                $this->createAttributeRequirementsByChannel($family, $attributeCodes, $channelCode)
            );
        }

        $requirements = $this->addMissingIdentifierRequirements($family, $requirements);

        $this->removeRequirements($family, $requirements, $oldRequirements);

        $family->setAttributeRequirements($requirements);
    }

    /**
     * @param FamilyInterface $family
     *
     * @return AttributeRequirementInterface[]
     */
    protected function getExistingIdentifierRequirements(FamilyInterface $family)
    {
        $identifierReqs = [];
        $existingRequirements = $family->getAttributeRequirements();
        foreach ($existingRequirements as $requirement) {
            if (AttributeTypes::IDENTIFIER === $requirement->getAttribute()->getAttributeType()) {
                $identifierReqs[] = $requirement;
            }
        }

        return $identifierReqs;
    }

    /**
     * Creates attribute requirements for the given channel but skip identifiers
     *
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
                    'updater',
                    'family',
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
     * @param FamilyInterface                 $family
     * @param AttributeRequirementInterface[] $requirements
     *
     * @throws InvalidPropertyException
     *
     * @return AttributeRequirementInterface[]
     */
    protected function addMissingIdentifierRequirements(FamilyInterface $family, array $requirements)
    {
        $channelCodes = $this->channelRepository->getChannelCodes();
        $existingChannelCode = [];
        foreach ($requirements as $requirement) {
            if (AttributeTypes::IDENTIFIER === $requirement->getAttribute()->getAttributeType()) {
                $existingChannelCode[] = $requirement->getChannelCode();
            }
        }
        $missingChannelCodes = array_diff($channelCodes, $existingChannelCode);
        $identifier = $this->attributeRepository->getIdentifier();
        foreach ($missingChannelCodes as $channelCode) {
            $requirements[] = $this->createAttributeRequirement($family, $identifier, $channelCode);
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
                'updater',
                'family',
                $channelCode
            );
        }

        $requirement = $this->requirementRepo->findOneBy(
            ['attribute' => $attribute->getId(), 'channel' => $channel->getId(), 'family' => $family->getId()]
        );

        if (null === $requirement) {
            $requirement = $this->attrRequiFactory->createAttributeRequirement($attribute, $channel, true);
        }

        $requirement->setRequired(true);

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
                    'updater',
                    'family',
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
                'updater',
                'family',
                $data
            );
        }
    }

    /**
     * @param FamilyInterface $family
     * @param array           $requirements
     * @param array           $oldRequirements
     */
    protected function removeRequirements(
        FamilyInterface $family,
        array $requirements,
        array $oldRequirements
    ) {
        $checkRequirements = new ArrayCollection($requirements);
        foreach ($oldRequirements as $requirement) {
            if (!$checkRequirements->contains($requirement)) {
                $family->removeAttributeRequirement($requirement);
            }
        }
    }
}
