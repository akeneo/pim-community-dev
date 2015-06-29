<?php

namespace Pim\Bundle\CatalogBundle\Builder;

use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Factory\AttributeRequirementFactory;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;

/**
 * Family builder
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyBuilder implements FamilyBuilderInterface
{
    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var AttributeRequirementFactory */
    protected $attRequiFactory;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param ChannelRepositoryInterface   $channelRepository
     * @param AttributeRequirementFactory  $attRequiFactory
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        AttributeRequirementFactory $attRequiFactory,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->channelRepository   = $channelRepository;
        $this->attRequiFactory     = $attRequiFactory;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param bool $withIdentifier
     *
     * @return FamilyInterface
     */
    public function createFamily($withIdentifier = false)
    {
        $family = new Family();

        if ($withIdentifier) {
            $identifier = $this->attributeRepository->getIdentifier();

            $family->addAttribute($identifier);
            $family->setAttributeAsLabel($identifier);

            foreach ($this->getChannels() as $channel) {
                $requirement = $this->attRequiFactory->createAttributeRequirement($identifier, $channel, true);
                $family->addAttributeRequirement($requirement);
            }
        }

        return $family;
    }

    /**
     * {@inheritdoc}
     */
    public function setLabels(FamilyInterface $family, array $data)
    {
        foreach ($data as $localeCode => $label) {
            $family->setLocale($localeCode);
            $translation = $family->getTranslation();
            $translation->setLabel($label);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributeRequirements(FamilyInterface $family, array $data)
    {
        $requirements = [];
        foreach ($data as $channelCode => $attributeCodes) {
            foreach ($attributeCodes as $attributeCode) {
                if (null !== $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode)) {
                    if (null !== $channel = $this->channelRepository->findOneByIdentifier($channelCode)) {
                        $requirements[] = $this->attRequiFactory
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
     * {@inheritdoc}
     */
    public function addAttributes(FamilyInterface $family, array $data)
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
     * @return \Pim\Bundle\CatalogBundle\Entity\Channel[]
     */
    protected function getChannels()
    {
        return $this->channelRepository->findBy([]);
    }
}
