<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\CurrencyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;

/**
 * Channel entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Channel implements ChannelInterface
{
    /** @var int $id */
    protected $id;

    /** @var string $code */
    protected $code;

    /** @var string $label */
    protected $label;

    /** @var CategoryInterface $category */
    protected $category;

    /** @var ArrayCollection $currencies */
    protected $currencies;

    /** @var ArrayCollection $locales */
    protected $locales;

    /** @var array $conversionUnits */
    protected $conversionUnits = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->currencies = new ArrayCollection();
        $this->locales    = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param int $id
     *
     * @return Channel
     */
    public function setId($id)
    {
        $this->id = $id;

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
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * {@inheritdoc}
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * {@inheritdoc}
     */
    public function setCategory(CategoryInterface $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrencies()
    {
        return $this->currencies;
    }

    /**
     * {@inheritdoc}
     */
    public function addCurrency(CurrencyInterface $currency)
    {
        $this->currencies[] = $currency;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeCurrency(CurrencyInterface $currency)
    {
        $this->currencies->removeElement($currency);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocales()
    {
        return $this->locales;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocaleCodes()
    {
        return $this->locales->map(
            function ($locale) {
                return $locale->getCode();
            }
        )->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function addLocale(LocaleInterface $locale)
    {
        if (!$this->hasLocale($locale)) {
            $this->locales[] = $locale;
            $locale->addChannel($this);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeLocale(LocaleInterface $locale)
    {
        $this->locales->removeElement($locale);
        $locale->removeChannel($this);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasLocale(LocaleInterface $locale)
    {
        return $this->locales->contains($locale);
    }

    /**
     * {@inheritdoc}
     */
    public function setConversionUnits(array $conversionUnits)
    {
        $this->conversionUnits = $conversionUnits;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getConversionUnits()
    {
        return $this->conversionUnits;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->label;
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
    public function getChoiceValue()
    {
        return $this->getCode();
    }

    /**
     * {@inheritdoc}
     */
    public function getChoiceLabel()
    {
        return $this->getLabel();
    }
}
