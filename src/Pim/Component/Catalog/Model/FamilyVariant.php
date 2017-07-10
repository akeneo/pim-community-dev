<?php

namespace Pim\Component\Catalog\Model;

use Akeneo\Component\Localization\Model\TranslationInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * A variant in a family defines the structure for the products with variants: Common attributes, Specific or variant attributes
 * Variant axes
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyVariant implements FamilyVariantInterface
{
    /** @var int */
    private $id;

    /** @var string */
    private $code;

    /** @var FamilyInterface */
    private $family;

    /** @var Collection */
    private $variantAttributeSets;

    /** @var string */
    private $locale;

    /** @var Collection */
    private $translations;

    public function __construct()
    {
        $this->variantAttributeSets = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): int
    {
        return $this->id;
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
    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommonAttributes(): CommonAttributeCollection
    {
        $commonAttributes = CommonAttributeCollection::fromCollection($this->family->getAttributes());

        foreach ($this->variantAttributeSets as $variantAttributeSet) {
            foreach ($variantAttributeSet->getAxes() as $axis) {
                $commonAttributes->removeElement($axis);
            }

            foreach ($variantAttributeSet->getAttributes() as $attribute) {
                $commonAttributes->removeElement($attribute);
            }
        }

        return $commonAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariantAttributeSet(int $level): VariantAttributeSetInterface
    {
        if ($level <= 0) {
            throw new \InvalidArgumentException('The level must be greater than 0');
        }

        return $this->variantAttributeSets->get($level);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes(): Collection
    {
        $attributes = [];
        foreach ($this->variantAttributeSets as $attributeSet) {
            $variantAttributeSetAttributes = $attributeSet->getAttributes()->toArray();
            $attributes = array_merge($attributes, $variantAttributeSetAttributes);
        }

        return new ArrayCollection($attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function getAxes(): Collection
    {
        $axes = [];
        foreach ($this->variantAttributeSets as $attributeSet) {
            $variantSetAxes = $attributeSet->getAxes()->toArray();
            $axes = array_merge($axes, $variantSetAxes);
        }

        return new ArrayCollection($axes);
    }

    /**
     * {@inheritdoc}
     */
    public function addVariantAttributeSet(int $level, VariantAttributeSetInterface $variantAttributeSet): void
    {
        if ($level <= 0) {
            throw new \InvalidArgumentException('The level must be greater than 0');
        }

        $this->variantAttributeSets->set($level, $variantAttributeSet);
    }

    /**
     * {@inheritdoc}
     */
    public function getFamily(): FamilyInterface
    {
        return $this->family;
    }

    /**
     * {@inheritdoc}
     */
    public function setFamily(FamilyInterface $family): void
    {
        $this->family = $family;
    }

    /**
     * {@inheritdoc}
     */
    public function getLevel(): int
    {
        return $this->variantAttributeSets->count();
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
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
    public function getTranslations()
    {
        return $this->translations;
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
        return FamilyVariantTranslation::class;
    }
}
