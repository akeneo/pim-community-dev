<?php

namespace Akeneo\Pim\Enrichment\Component\Category\Model;

use Akeneo\Tool\Component\Classification\Model\Category as BaseCategory;
use Akeneo\Tool\Component\Localization\Model\TranslationInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Category class allowing to organize a flexible product class into trees
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @Gedmo\Tree(type="nested")
 */
class Category extends BaseCategory implements CategoryInterface
{
    /** @var Collection of ProductInterface */
    protected ArrayCollection $products;

    /** @var Collection of ProductModelInterface */
    protected ArrayCollection $productModels;

    /**
     * Used locale to override Translation listener's locale
     * this is not a mapped field of entity metadata, just a simple property
     *
     * @var string
     */
    protected string $locale;

    /** @var ArrayCollection of CategoryTranslation */
    protected ArrayCollection $translations;

    /** @var ArrayCollection of Channel */
    protected ArrayCollection $channels;

    /** @var \DateTime */
    protected $created;

    private \DateTime $updated;

    /** @Gedmo\TreeLeft */
    protected int $left;
    /** @Gedmo\TreeLevel */
    protected int $level;
    /** @Gedmo\TreeRight */
    protected int $right;
    /** @Gedmo\TreeRoot */
    protected int $root;
    /** @Gedmo\TreeParent */
    protected CategoryInterface $parent;

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
        return $this->products->count() !== 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Get products count
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

    /**
     * {@inheritdoc}
     */
    public function getTranslation(?string $locale = null): ?CategoryTranslationInterface
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
        return CategoryTranslation::class;
    }

    public function getLabel(): string
    {
        $translated = ($this->getTranslation()) ? $this->getTranslation()->getLabel() : null;

        return ($translated !== '' && $translated !== null) ? $translated : '[' . $this->getCode() . ']';
    }

    /**
     * Set label
     *
     * @param string $label
     *
     * @return CategoryInterface
     */
    public function setLabel($label)
    {
        $this->getTranslation()->setLabel($label);

        return $this;
    }

    /**
     * Returns the channels linked to the category
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
