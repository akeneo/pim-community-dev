<?php

namespace Akeneo\Pim\Structure\Component\Model;

use Akeneo\Tool\Component\Versioning\Model\TimestampableInterface;
use Akeneo\Tool\Component\Localization\Model\TranslatableInterface;
use Akeneo\Tool\Component\Localization\Model\AbstractTranslation;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\Localization\Model\TranslationInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

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
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get created datetime
     */
    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    /**
     * Set created datetime
     *
     * @param \DateTime $created
     */
    public function setCreated(\DateTime $created): TimestampableInterface
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get updated datetime
     */
    public function getUpdated(): \DateTime
    {
        return $this->updated;
    }

    /**
     * Set updated datetime
     *
     * @param \DateTime $updated
     */
    public function setUpdated(\DateTime $updated): TimestampableInterface
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode(string $code): FamilyInterface
    {
        $this->code = $code;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttribute(AttributeInterface $attribute): FamilyInterface
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
    public function removeAttribute(AttributeInterface $attribute): FamilyInterface
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
    public function getAttributes(): \Doctrine\Common\Collections\Collection
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeCodes(): array
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
    public function getGroupedAttributes(): array
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
    public function hasAttribute(AttributeInterface $attribute): bool
    {
        return $this->hasAttributeCode($attribute->getCode());
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttributeCode(string $attributeCode): bool
    {
        return in_array($attributeCode, $this->getAttributeCodes());
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributeAsLabel(AttributeInterface $attributeAsLabel): FamilyInterface
    {
        $this->attributeAsLabel = $attributeAsLabel;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeAsLabel(): ?\Akeneo\Pim\Structure\Component\Model\AttributeInterface
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
    public function getAttributeAsImage(): \Akeneo\Pim\Structure\Component\Model\AttributeInterface
    {
        return $this->attributeAsImage;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeAsLabelChoices(): array
    {
        return $this->attributes->filter(
            fn($attribute) => in_array(
                $attribute->getType(),
                [AttributeTypes::TEXT, AttributeTypes::IDENTIFIER]
            )
        )->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale(?string $locale): TranslatableInterface
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslations(): ArrayCollection
    {
        return $this->translations;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslation(?string $locale = null): AbstractTranslation
    {
        $locale = ($locale) ? $locale : $this->locale;
        if (null === $locale) {
            return null;
        }
        if ($this->translations->containsKey($locale)) {
            return $this->translations->get($locale);
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
    public function addTranslation(TranslationInterface $translation): TranslatableInterface
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->set($translation->getLocale(), $translation);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeTranslation(TranslationInterface $translation): TranslatableInterface
    {
        $this->translations->removeElement($translation);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslationFQCN(): string
    {
        return FamilyTranslation::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        $translated = $this->getTranslation() !== null ? $this->getTranslation()->getLabel() : null;

        return ($translated !== '' && $translated !== null) ? $translated : '['.$this->getCode().']';
    }

    /**
     * {@inheritdoc}
     */
    public function setLabel(string $label): FamilyInterface
    {
        $this->getTranslation()->setLabel($label);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeRequirement(AttributeRequirementInterface $requirement): FamilyInterface
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
    public function removeAttributeRequirement(AttributeRequirementInterface $requirement): FamilyInterface
    {
        $this->requirements->removeElement($requirement);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributeRequirements(array $requirements): FamilyInterface
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
    public function getAttributeRequirements(): array
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
    public function getAttributeRequirementKey(AttributeRequirementInterface $requirement): string
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
    public function getReference(): string
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
