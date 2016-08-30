<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Model;

use Akeneo\Component\Classification\Model\CategoryInterface as BaseCategoryInterface;
use Akeneo\Component\Classification\Model\TagInterface as BaseTagInterface;
use Akeneo\Component\Versioning\Model\VersionableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;

/**
 * Product asset
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class Asset implements AssetInterface, VersionableInterface
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $code;

    /** @var ArrayCollection of BaseCategoryInterface */
    protected $categories;

    /** @var string */
    protected $description;

    /** @var ArrayCollection of ReferenceInterface */
    protected $references;

    /** @var bool */
    protected $enabled;

    /** @var \Datetime */
    protected $endOfUseAt;

    /** @var \Datetime */
    protected $createdAt;

    /** @var \Datetime */
    protected $updatedAt;

    /** @var ArrayCollection of BaseTagInterface */
    protected $tags;

    public function __construct()
    {
        $this->references = new ArrayCollection();
        $this->enabled = true;
        $this->createdAt = new \Datetime();
        $this->updatedAt = new \Datetime();
        $this->tags = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
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
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getReferences()
    {
        return $this->references;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocales()
    {
        $locales = [];

        foreach ($this->getReferences() as $reference) {
            if (null !== $reference->getLocale()) {
                $locales[$reference->getLocale()->getCode()] = $reference->getLocale();
            }
        }

        return $locales;
    }

    /**
     * {@inheritdoc}
     */
    public function isLocalizable()
    {
        return count($this->getReferences()) > 1;
    }

    /**
     * {@inheritdoc}
     */
    public function setReferences(ArrayCollection $references)
    {
        $this->references = $references;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addReference(ReferenceInterface $reference)
    {
        if (!$this->references->contains($reference)) {
            $this->references->add($reference);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeReference(ReferenceInterface $reference)
    {
        if ($this->references->contains($reference)) {
            $this->references->removeElement($reference);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    public function getReference(LocaleInterface $locale = null)
    {
        if ($this->getReferences()->isEmpty()) {
            return null;
        }

        if (!$this->isLocalizable()) {
            return $this->getReferences()[0];
        }

        if (null === $locale) {
            throw new \LogicException(sprintf(
                'Cannot retrieve the reference of the localizable asset "%s" if no locale is specified',
                $this->getCode()
            ));
        }

        foreach ($this->getReferences() as $reference) {
            if ($locale === $reference->getLocale()) {
                return $reference;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function hasReference(LocaleInterface $locale = null)
    {
        return null !== $this->getReference($locale);
    }

    /**
     * {@inheritdoc}
     */
    public function getVariations()
    {
        $variations = [];

        foreach ($this->getReferences() as $reference) {
            $variations = array_merge($variations, $reference->getVariations()->toArray());
        }

        return $variations;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariation(ChannelInterface $channel, LocaleInterface $locale = null)
    {
        foreach ($this->getVariations() as $variation) {
            if ($variation->getChannel() === $channel && $variation->getLocale() === $locale) {
                return $variation;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function hasVariation(ChannelInterface $channel, LocaleInterface $locale = null)
    {
        return null !== $this->getVariation($channel, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabled($isEnabled)
    {
        $this->enabled = $isEnabled;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEndOfUseAt()
    {
        return $this->endOfUseAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setEndOfUseAt($endOfUseAt)
    {
        $this->endOfUseAt = $endOfUseAt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt(\Datetime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt(\Datetime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Look for the variation corresponding to the specified channel and return its file info.
     * If the asset is localizable the search will be done in the variations of the specified locale.
     * If the reference has no variations (e.g. not generated yet), fallback on reference file info.
     *
     * {@inheritdoc}
     */
    public function getFileForContext(ChannelInterface $channel, LocaleInterface $locale = null)
    {
        $reference = $this->getReference($locale);

        if (null === $reference) {
            return null;
        }

        if (null === $variation = $reference->getVariation($channel)) {
            return $reference->getFileInfo();
        }

        return $variation->getFileInfo();
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public static function getLabelProperty()
    {
        return 'code';
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getCode();
    }

    /**
     * {@inheritdoc}
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * {@inheritdoc}
     */
    public function removeTag(BaseTagInterface $tag)
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addTag(BaseTagInterface $tag)
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTagCodes()
    {
        $tags = [];
        foreach ($this->getTags() as $tag) {
            $tags[] = $tag->getCode();
        }
        sort($tags);

        return $tags;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * {@inheritdoc}
     */
    public function removeCategory(BaseCategoryInterface $category)
    {
        $this->categories->removeElement($category);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addCategory(BaseCategoryInterface $category)
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryCodes()
    {
        $codes = [];
        foreach ($this->getCategories() as $category) {
            $codes[] = $category->getCode();
        }
        sort($codes);

        return $codes;
    }
}
