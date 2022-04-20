<?php

namespace Akeneo\Category\Domain\Model;

use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\Classification\Model\Category as BaseCategory;
use Akeneo\Tool\Component\Localization\Model\TranslationInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Category class allowing to organize a flexible product class into trees.
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Category extends BaseCategory implements CategoryInterface
{
    /** @var Collection<int, ProductInterface> */
    protected Collection $products;

    /** @var Collection<int, ProductModelInterface> */
    protected Collection $productModels;

    /**
     * Used locale to override Translation listener's locale
     * this is not a mapped field of entity metadata, just a simple property.
     *
     * @var string
     */
    protected $locale;

    /** @var Collection<int, TranslationInterface> */
    protected Collection $translations;

    /** @var Collection<int, Channel> */
    protected Collection $channels;

    /** @var \DateTime */
    protected $created;

    private \DateTime $updated;

    public function __construct()
    {
        parent::__construct();

        $this->products = new ArrayCollection();
        $this->productModels = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->channels = new ArrayCollection();
        $this->updated = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * {@inheritdoc}
     */
    public function hasProducts()
    {
        return 0 !== $this->products->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    /**
     * Get products count.
     *
     * @return int
     */
    public function getProductsCount()
    {
        return $this->products->count();
    }

    /**
     * {@inheritdoc}
     */
    public function hasProductModels(): bool
    {
        return 0 !== $this->productModels->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getProductModels(): Collection
    {
        return $this->productModels;
    }

    /**
     * Get created date.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    public function setUpdated(\DateTime $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    public function getUpdated(): \DateTime
    {
        return $this->updated;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    public function getTranslation(?string $locale = null): ?TranslationInterface
    {
        $locale = ($locale) ? $locale : $this->locale;
        if (null == $locale) {
            return null;
        }
        foreach ($this->getTranslations() as $translation) {
            if (strtolower($translation->getLocale()) === strtolower($locale)) {
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
    public function getTranslations(): Collection
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
        return CategoryTranslation::class;
    }

    public function getLabel(): string
    {
        $translation = $this->getTranslation();

        $translated = null;
        if ($translation instanceof CategoryTranslationInterface) {
            $translated = $translation->getLabel();
        }

        return ('' !== $translated && null !== $translated) ? $translated : '['.$this->getCode().']';
    }

    /**
     * Set label.
     *
     * @param string $label
     *
     * @return CategoryInterface
     */
    public function setLabel($label)
    {
        $translation = $this->getTranslation();

        if ($translation instanceof CategoryTranslationInterface) {
            $translation->setLabel($label);
        }

        return $this;
    }

    /**
     * Returns the channels linked to the category.
     */
    public function getChannels(): Collection
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
    public function getReference()
    {
        return $this->code;
    }
}
