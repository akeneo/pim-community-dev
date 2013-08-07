<?php

namespace Pim\Bundle\ConfigBundle\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\ProductBundle\Entity\Category;
use Pim\Bundle\ConfigBundle\Entity\Currency;
use Pim\Bundle\ConfigBundle\Entity\Locale;

/**
 * Channel entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="pim_channel")
 * @ORM\Entity(repositoryClass="Pim\Bundle\ConfigBundle\Entity\Repository\ChannelRepository")
 * @UniqueEntity("code")
 */
class Channel
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $code
     *
     * @ORM\Column(name="code", type="string", length=50, unique=true)
     */
    protected $code;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=100)
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\ProductBundle\Entity\Category")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    protected $category;

    /**
     * @var ArrayCollection $currencies
     *
     * @ORM\ManyToMany(targetEntity="Pim\Bundle\ConfigBundle\Entity\Currency", cascade={"persist"})
     * @ORM\JoinTable(
     *    name="pim_channel_currency",
     *    joinColumns={@ORM\JoinColumn(name="channel_id", referencedColumnName="id", onDelete="CASCADE")},
     *    inverseJoinColumns={@ORM\JoinColumn(name="currency_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $currencies;

    /**
     * @var ArrayCollection $locales
     *
     * @ORM\ManyToMany(targetEntity="Pim\Bundle\ConfigBundle\Entity\Locale", inversedBy="channels", cascade={"persist"})
     * @ORM\JoinTable(
     *    name="pim_channel_locale",
     *    joinColumns={@ORM\JoinColumn(name="channel_id", referencedColumnName="id", onDelete="CASCADE")},
     *    inverseJoinColumns={@ORM\JoinColumn(name="locale_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $locales;

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
     * @return \Pim\Bundle\ConfigBundle\Entity\Channel
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
     * @return \Pim\Bundle\ConfigBundle\Entity\Channel
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return \Pim\Bundle\ConfigBundle\Entity\Channel
     */
    public function setName($name)
    {
        $this->name = $name;

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
     * Add locale
     *
     * @param Locale $locale
     *
     * @return Channel
     */
    public function addLocale(Locale $locale)
    {
        $this->locales[] = $locale;
        $locale->activate();

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
        $locale->deactivate();

        return $this;
    }
}
