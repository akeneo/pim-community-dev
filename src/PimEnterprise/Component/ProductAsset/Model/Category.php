<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\TranslationBundle\Entity\AbstractTranslation;
use Akeneo\Component\Classification\Model\Category as BaseCategory;

/**
 * Category for assets
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 *
 * TODO: ExclusionPolicy("all") ? use JMS\Serializer\Annotation\ExclusionPolicy;
 */
class Category extends BaseCategory implements CategoryInterface
{
    /** @var ArrayCollection of AssetInterface */
    protected $assets;

    /**
     * Used locale to override Translation listener's locale
     * this is not a mapped field of entity metadata, just a simple property
     *
     * @var string
     */
    protected $locale;

    /** @var ArrayCollection of CategoryTranslation */
    protected $translations;

    /** @var \DateTime */
    protected $created;

    public function __construct()
    {
        parent::__construct();

        $this->assets       = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function hasAssets()
    {
        return $this->assets->count() !== 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssets()
    {
        return $this->assets;
    }

    /**
     * @return int
     */
    public function getAssetsCount()
    {
        return $this->assets->count();
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
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
    public function getTranslation($locale = null)
    {
        $locale = ($locale) ? $locale : $this->locale;
        if (!$locale) {
            return null;
        }
        foreach ($this->getTranslations() as $translation) {
            if ($translation->getLocale() == $locale) {
                return $translation;
            }
        }

        $translationClass = $this->getTranslationFQCN();
        $translation      = new $translationClass();
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
    public function addTranslation(AbstractTranslation $translation)
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeTranslation(AbstractTranslation $translation)
    {
        $this->translations->removeElement($translation);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslationFQCN()
    {
        return 'PimEnterprise\Component\ProductAsset\Model\CategoryTranslation';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        $translated = (null !== $this->getTranslation()) ? $this->getTranslation()->getLabel() : null;

        return ('' !== $translated && null !== $translated) ? $translated : '['.$this->getCode().']';
    }

    /**
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
