<?php

namespace Akeneo\Pim\Enrichment\Component\Category\Model;

use Akeneo\Tool\Component\Localization\Model\TranslatableInterface;
use Akeneo\Tool\Component\Localization\Model\AbstractTranslation;
use Akeneo\Tool\Component\Classification\Model\Category as BaseCategory;
use Akeneo\Tool\Component\Localization\Model\TranslationInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Category class allowing to organize a flexible product class into trees
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Category extends BaseCategory implements CategoryInterface
{
    /** @var Collection of ProductInterface */
    protected $products;

    /** @var Collection of ProductModelInterface */
    protected $productModels;

    /**
     * Used locale to override Translation listener's locale
     * this is not a mapped field of entity metadata, just a simple property
     *
     * @var string
     */
    protected $locale;

    /** @var ArrayCollection of CategoryTranslation */
    protected $translations;

    /** @var ArrayCollection of Channel */
    protected $channels;

    /** @var \DateTime */
    protected $created;

    public function __construct()
    {
        parent::__construct();

        $this->products = new ArrayCollection();
        $this->productModels = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->channels = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function hasProducts(): bool
    {
        return $this->products->count() !== 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getProducts(): array
    {
        return $this->products;
    }

    /**
     * Get products count
     */
    public function getProductsCount(): int
    {
        return $this->products->count();
    }

    /**
     * {@inheritdoc}
     */
    public function hasProductModels(): bool
    {
        return $this->productModels->count() !== 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductModels(): Collection
    {
        return $this->productModels;
    }

    /**
     * Get created date
     */
    public function getCreated(): \DateTime
    {
        return $this->created;
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
    public function getTranslation(?string $locale = null): AbstractTranslation
    {
        $locale = ($locale) ? $locale : $this->locale;
        if (null === $locale) {
            return null;
        }
        foreach ($this->getTranslations() as $translation) {
            if ($translation->getLocale() === $locale) {
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
    public function getTranslations(): \Doctrine\Common\Collections\ArrayCollection
    {
        return $this->translations;
    }

    /**
     * {@inheritdoc}
     */
    public function addTranslation(TranslationInterface $translation): TranslatableInterface
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
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
        return CategoryTranslation::class;
    }

    public function getLabel(): string
    {
        $translated = ($this->getTranslation() !== null) ? $this->getTranslation()->getLabel() : null;

        return ($translated !== '' && $translated !== null) ? $translated : '['.$this->getCode().']';
    }

    /**
     * Set label
     *
     * @param string $label
     */
    public function setLabel(string $label): self
    {
        $this->getTranslation()->setLabel($label);

        return $this;
    }

    /**
     * Returns the channels linked to the category
     */
    public function getChannels(): \Doctrine\Common\Collections\ArrayCollection
    {
        return $this->channels;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getLabel();
    }

    /**
     * {@inheritdoc}
     */
    public function getReference(): string
    {
        return $this->code;
    }
}
