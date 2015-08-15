<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Bundle\CatalogBundle\Factory\AttributeRequirementFactory;
use Pim\Bundle\CatalogBundle\Factory\FamilyFactory;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeRequirementInterface;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
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

    /** @var FamilyFactory */
    protected $familyFactory;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var AttributeRequirementFactory */
    protected $attrRequiFactory;

    /**
     * @param IdentifiableObjectRepositoryInterface $familyRepository
     * @param FamilyFactory                         $familyFactory
     * @param AttributeRepositoryInterface          $attributeRepository
     * @param ChannelRepositoryInterface            $channelRepository
     * @param AttributeRequirementFactory           $attrRequiFactory
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $familyRepository,
        FamilyFactory $familyFactory,
        AttributeRepositoryInterface $attributeRepository,
        ChannelRepositoryInterface $channelRepository,
        AttributeRequirementFactory $attrRequiFactory
    ) {
        $this->accessor            = PropertyAccess::createPropertyAccessor();
        $this->familyRepository    = $familyRepository;
        $this->familyFactory       = $familyFactory;
        $this->attributeRepository = $attributeRepository;
        $this->channelRepository   = $channelRepository;
        $this->attrRequiFactory    = $attrRequiFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function update($family, array $data, array $options = [])
    {
        if (!$family instanceof FamilyInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Pim\Bundle\CatalogBundle\Model\FamilyInterface", "%s" provided.',
                    ClassUtils::getClass($family)
                )
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
     */
    protected function setData(FamilyInterface $family, $field, $data)
    {
        switch ($field) {
            case 'labels':
                $this->setLabels($family, $data);
                break;
            case 'requirements':
                $this->setAttributeRequirements($family, $data);
                break;
            case 'attributes':
                $this->addAttributes($family, $data);
                break;
            case 'attribute_as_label':
                $this->setAttributeAsLabel($family, $data);
                break;
            default:
                $this->accessor->setValue($family, $field, $data);
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
     */
    protected function setAttributeRequirements(FamilyInterface $family, array $data)
    {
        $requirements = $this->getExistingIdentifierRequirements($family);
        foreach ($data as $channelCode => $attributeCodes) {
            $requirements = array_merge(
                $requirements,
                $this->createAttributeRequirementsByChannel($attributeCodes, $channelCode)
            );
        }
        $requirements = $this->addMissingIdentifierRequirements($requirements);

        $family->setAttributeRequirements($requirements);
    }

    /**
     * @param FamilyInterface $family
     *
     * @return AttributeRequirementInterface[]
     */
    protected function getExistingIdentifierRequirements(FamilyInterface $family)
    {
        $identifierRequirements = [];
        $existingRequirements = $family->getAttributeRequirements();
        foreach ($existingRequirements as $requirement) {
            if (AttributeTypes::IDENTIFIER === $requirement->getAttribute()->getAttributeType()) {
                $identifierRequirements[] = $requirement;
            }
        }

        return $identifierRequirements;
    }

    /**
     * Creates attribute requirements for the given channel but skip identifiers
     *
     * @param array  $attributeCodes
     * @param string $channelCode
     *
     * @throws \InvalidArgumentException
     *
     * @return AttributeRequirementInterface[]
     */
    protected function createAttributeRequirementsByChannel(array $attributeCodes, $channelCode)
    {
        $requirements  = [];
        foreach ($attributeCodes as $attributeCode) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
            if (null === $attribute) {
                throw new \InvalidArgumentException(
                    sprintf('Attribute with "%s" code does not exist', $attributeCode)
                );
            }
            if (AttributeTypes::IDENTIFIER !== $attribute->getAttributeType()) {
                $requirements[] = $this->createAttributeRequirement($attribute, $channelCode);
            }
        }

        return $requirements;
    }

    /**
     * @param AttributeRequirementInterface[] $requirements
     *
     * @return AttributeRequirementInterface[]
     */
    protected function addMissingIdentifierRequirements(array $requirements)
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
            $requirements[] = $this->createAttributeRequirement($identifier, $channelCode);
        }

        return $requirements;
    }

    /**
     * @param AttributeInterface $attribute
     * @param string             $channelCode
     *
     * @throws \InvalidArgumentException
     *
     * @return AttributeRequirementInterface
     */
    protected function createAttributeRequirement(AttributeInterface $attribute, $channelCode)
    {
        $channel = $this->channelRepository->findOneByIdentifier($channelCode);
        if (null === $channel) {
            throw new \InvalidArgumentException(
                sprintf('Channel with "%s" code does not exist', $channelCode)
            );
        }

        return $this->attrRequiFactory->createAttributeRequirement($attribute, $channel, true);
    }

    /**
     * @param FamilyInterface $family
     * @param array           $data
     *
     * @throws \InvalidArgumentException
     */
    protected function addAttributes(FamilyInterface $family, array $data)
    {
        foreach ($data as $attributeCode) {
            if (null !== $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode)) {
                $family->addAttribute($attribute);
            } else {
                throw new \InvalidArgumentException(
                    sprintf('Attribute with "%s" code does not exist', $attributeCode)
                );
            }
        }
    }

    /**
     * @param FamilyInterface $family
     * @param string          $data
     *
     * @throws \InvalidArgumentException
     */
    protected function setAttributeAsLabel(FamilyInterface $family, $data)
    {
        if (null !== $attribute = $this->attributeRepository->findOneByIdentifier($data)) {
            $family->setAttributeAsLabel($attribute);
        } else {
            throw new \InvalidArgumentException(
                sprintf('Attribute with "%s" code does not exist', $data)
            );
        }
    }
}
