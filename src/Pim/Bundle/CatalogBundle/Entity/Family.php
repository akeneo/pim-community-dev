<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Akeneo\Component\Localization\Model\TranslationInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeRequirementInterface;
use Pim\Component\Catalog\Model\FamilyInterface;

/**
 * Family entity
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Family implements FamilyInterface
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $code;

    /** @var Collection */
    protected $attributes;

    /**
     * Used locale to override Translation listener's locale
     * this is not a mapped field of entity metadata, just a simple property
     *
     * @var string
     */
    protected $locale;

    /** @var Collection */
    protected $translations;

    /** @var AttributeInterface */
    protected $attributeAsLabel;

    /** @var AttributeInterface */
    protected $attributeAsImage;

    /** @var Collection */
    protected $requirements;

    /** @var \DateTime */
    protected $created;

    /** @var \DateTime */
    protected $updated;

    /** @var Collection */
    protected $familyVariants;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->attributes = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->requirements = new ArrayCollection();
        $this->familyVariants = new ArrayCollection();
    }

    /**
     * Returns the label of the family
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getLabel();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get created datetime
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set created datetime
     *
     * @param \DateTime $created
     *
     * @return Family
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get updated datetime
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set updated datetime
     *
     * @param \DateTime $updated
     *
     * @return Family
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttribute(AttributeInterface $attribute)
    {
        if (!$this->attributes->contains($attribute)) {
            $this->attributes->add($attribute);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    public function removeAttribute(AttributeInterface $attribute)
    {
        if (AttributeTypes::IDENTIFIER === $attribute->getType()) {
            throw new \InvalidArgumentException('Identifier cannot be removed from a family.');
        }

        $this->attributes->removeElement($attribute);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeCodes()
    {
        $codes = [];
        foreach ($this->attributes as $attribute) {
            $codes[] = $attribute->getCode();
        }

        return $codes;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupedAttributes()
    {
        $result = [];
        foreach ($this->attributes as $attribute) {
            $result[(string) $attribute->getGroup()][] = $attribute;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttribute(AttributeInterface $attribute)
    {
        return $this->hasAttributeCode($attribute->getCode());
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttributeCode($attributeCode)
    {
        return in_array($attributeCode, $this->getAttributeCodes());
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributeAsLabel(AttributeInterface $attributeAsLabel)
    {
        $this->attributeAsLabel = $attributeAsLabel;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeAsLabel()
    {
        return $this->attributeAsLabel;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributeAsImage(?AttributeInterface $attributeAsImage): FamilyInterface
    {
        $this->attributeAsImage = $attributeAsImage;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeAsImage(): ?AttributeInterface
    {
        return $this->attributeAsImage;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeAsLabelChoices()
    {
        return $this->attributes->filter(
            function ($attribute) {
                return in_array(
                    $attribute->getType(),
                    [AttributeTypes::TEXT, AttributeTypes::IDENTIFIER]
                );
            }
        )->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslation($locale = null)
    {
        $locale = ($locale) ? $locale : $this->locale;
        if (null === $locale) {
            return null;
        }
        foreach ($this->getTranslations() as $translation) {
            if ($translation->getLocale() == $locale) {
                return $translation;
            }
        }

        $translationClass = $this->getTranslationFQCN();
        $translation = new $translationClass();
        $translation->setLocale($locale);
        $translation->setForeignKey($this);
        $this->addTranslation($translation);

        return $translation;
    }

    /**
     * {@inheritdoc}
     */
    public function addTranslation(TranslationInterface $translation)
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeTranslation(TranslationInterface $translation)
    {
        $this->translations->removeElement($translation);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslationFQCN()
    {
        return 'Pim\Bundle\CatalogBundle\Entity\FamilyTranslation';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        $translated = $this->getTranslation() ? $this->getTranslation()->getLabel() : null;

        return ($translated !== '' && $translated !== null) ? $translated : '['.$this->getCode().']';
    }

    /**
     * {@inheritdoc}
     */
    public function setLabel($label)
    {
        $this->getTranslation()->setLabel($label);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeRequirement(AttributeRequirementInterface $requirement)
    {
        $requirementKey = $this->getAttributeRequirementKey($requirement);
        $requirements = $this->getAttributeRequirements();

        if (!isset($requirements[$requirementKey])) {
            $requirement->setFamily($this);
            $this->requirements->add($requirement);
        } else {
            $requirements[$requirementKey]->setRequired($requirement->isRequired());
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeAttributeRequirement(AttributeRequirementInterface $requirement)
    {
        $this->requirements->removeElement($requirement);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributeRequirements(array $requirements)
    {
        foreach ($requirements as $requirement) {
            $requirement->setFamily($this);
        }
        $this->requirements = new ArrayCollection($requirements);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeRequirements()
    {
        $result = [];

        foreach ($this->requirements as $requirement) {
            $key = $this->getAttributeRequirementKey($requirement);
            $result[$key] = $requirement;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeRequirementKey(AttributeRequirementInterface $requirement)
    {
        return sprintf(
            '%s_%s',
            $requirement->getAttributeCode(),
            $requirement->getChannelCode()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getReference()
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function getFamilyVariants(): Collection
    {
        return $this->familyVariants;
    }

    /**
     * {@inheritdoc}
     */
    public function setFamilyVariants(Collection $familyVariants): void
    {
        $this->familyVariants = $familyVariants;
    }
}
