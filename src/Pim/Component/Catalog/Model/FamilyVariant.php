<?php

namespace Pim\Component\Catalog\Model;

use Akeneo\Component\Localization\Model\TranslationInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
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

    /** @var ArrayCollection */
    private $variantAttributeSets;

    /** @var string */
    private $locale;

    /** @var ArrayCollection */
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
    public function setCode(string $code)
    {
        $this->code = $code;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommonAttributeSet(): AttributeSetInterface
    {
        return $this->variantAttributeSets->first();
    }

    /**
     * {@inheritdoc}
     */
    public function getVariantAttributeSet(int $level): AttributeSetInterface
    {
        if ($level <= 0) {
            throw new \InvalidArgumentException('The level must be greater than 0');
        }

        return $this->variantAttributeSets->get($level);
    }

    /**
     * {@inheritdoc}
     */
    public function getVariantAttributeSets($level = null): ArrayCollection
    {
        return new ArrayCollection($this->variantAttributeSets->slice(1));
    }

    /**
     * {@inheritdoc}
     */
    public function addVariantAttributeSet(int $level, AttributeSetInterface $variantAttributeSets)
    {
        if ($level <= 0) {
            throw new \InvalidArgumentException('The level must be greater than 0');
        }

        $this->variantAttributeSets->set($level, $variantAttributeSets);
    }

    /**
     * {@inheritdoc}
     */
    public function addCommonAttributeSet(AttributeSetInterface $variantAttributeSets)
    {
        $this->variantAttributeSets->set(0, $variantAttributeSets);
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
    public function setFamily(FamilyInterface $family)
    {
        $this->family = $family;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale(): string
    {
        return $this->locale;
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
