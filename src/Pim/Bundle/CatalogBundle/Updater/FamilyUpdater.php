<?php

namespace Pim\Bundle\CatalogBundle\Updater;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Factory\AttributeRequirementFactory;
use Pim\Bundle\CatalogBundle\Factory\FamilyFactory;
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
     * @param FamilyFactory $familyFactory
     * @param AttributeRepositoryInterface $attributeRepository
     * @param ChannelRepositoryInterface $channelRepository
     * @param AttributeRequirementFactory $attrRequiFactory
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
        if ('labels' === $field) {
            $this->setLabels($family, $data);
        } elseif ('requirements' === $field) {
            $this->setAttributeRequirements($family, $data);
        } elseif ('attributes' === $field) {
            $this->addAttributes($family, $data);
        } elseif ('attribute_as_label' === $field) {
            $this->setAttributeAsLabel($family, $data);
        } else {
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
        $requirements = [];
        foreach ($data as $channelCode => $attributeCodes) {
            foreach ($attributeCodes as $attributeCode) {
                if (null !== $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode)) {
                    if (null !== $channel = $this->channelRepository->findOneByIdentifier($channelCode)) {
                        $requirements[] = $this->attrRequiFactory
                            ->createAttributeRequirement($attribute, $channel, true);
                    } else {
                        throw new \InvalidArgumentException(
                            sprintf('Channel with "%s" code does not exist', $channelCode)
                        );
                    }
                } else {
                    throw new \InvalidArgumentException(
                        sprintf('Attribute with "%s" code does not exist', $attributeCode)
                    );
                }
            }
        }

        $family->setAttributeRequirements($requirements);
    }

    /**
     * @param FamilyInterface $family
     * @param array           $data
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
     * @param $data
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
