<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\ExclusionPolicy;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Entity\Currency;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Model\ReferableEntityInterface;

/**
 * Channel entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Config(
 *  defaultValues={
 *      "entity"={"label"="Channel", "plural_label"="Channels"},
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      }
 *  }
 * )
 *
 * @ExclusionPolicy("all")
 */
class Channel implements ReferableEntityInterface
{
    /** @var integer $id */
    protected $id;

    /** @var string $code */
    protected $code;

    /** @var string $label */
    protected $label;

    /** @var Category $category */
    protected $category;

    /** @var ArrayCollection $currencies */
    protected $currencies;

    /** @var ArrayCollection $locales */
    protected $locales;

    /** @var array $conversionUnits */
    protected $conversionUnits = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->currencies = new ArrayCollection();
        $this->locales    = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return Channel
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return Channel
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set label
     *
     * @param string $label
     *
     * @return Channel
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get category
     *
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set category
     *
     * @param Category $category
     *
     * @return Channel
     */
    public function setCategory(Category $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get currencies
     *
     * @return ArrayCollection
     */
    public function getCurrencies()
    {
        return $this->currencies;
    }

    /**
     * Add currency
     *
     * @param Currency $currency
     *
     * @return Channel
     */
    public function addCurrency(Currency $currency)
    {
        $this->currencies[] = $currency;

        return $this;
    }

    /**
     * Remove currency
     *
     * @param Currency $currency
     *
     * @return Channel
     */
    public function removeCurrency(Currency $currency)
    {
        $this->currencies->removeElement($currency);

        return $this;
    }

    /**
     * Get locales
     *
     * @return ArrayCollection
     */
    public function getLocales()
    {
        return $this->locales;
    }

    /**
     * Get locale codes
     *
     * @return array
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
     * Add locale
     *
     * @param Locale $locale
     *
     * @return Channel
     */
    public function addLocale(Locale $locale)
    {
        if (!$this->hasLocale($locale)) {
            $this->locales[] = $locale;
            $locale->addChannel($this);
        }

        return $this;
    }

    /**
     * Remove locale
     *
     * @param Locale $locale
     *
     * @return Channel
     */
    public function removeLocale(Locale $locale)
    {
        $this->locales->removeElement($locale);
        $locale->removeChannel($this);

        return $this;
    }

    /**
     * Predicate to know if a channel has a locale
     *
     * @param Locale $locale
     *
     * @return boolean
     */
    public function hasLocale(Locale $locale)
    {
        return $this->locales->contains($locale);
    }

    /**
     * Set conversion units
     *
     * @param array $conversionUnits
     *
     * @return Channel
     */
    public function setConversionUnits(array $conversionUnits)
    {
        $this->conversionUnits = $conversionUnits;

        return $this;
    }

    /**
     * Get conversion units
     *
     * @return array
     */
    public function getConversionUnits()
    {
        return $this->conversionUnits;
    }

    /**
     * To string
     *
     * @return string
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
}
