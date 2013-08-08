<?php

namespace Pim\Bundle\ConfigBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;
use Pim\Bundle\ConfigBundle\Validator\Constraints as PimAssert;

/**
 * Locale entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="pim_locale")
 * @ORM\Entity(repositoryClass="Pim\Bundle\ConfigBundle\Entity\Repository\LocaleRepository")
 * @UniqueEntity("code")
 * @PimAssert\ValidLocaleFallback
 */
class Locale
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
     * @ORM\Column(name="code", type="string", length=5, unique=true)
     */
    protected $code;

    /**
     * @var string $fallback
     *
     * @ORM\Column(name="fallback", type="string", length=10, nullable=true)
     */
    protected $fallback;

    /**
     * @var Currency $defaultCurrency
     *
     * @ORM\ManyToOne(targetEntity="Currency", inversedBy="locales")
     * @ORM\JoinColumn(name="default_currency_id", referencedColumnName="id")
     *
     * TODO : must be removed
     */
    protected $defaultCurrency;

    /**
     * @var boolean $activated
     *
     * @ORM\Column(name="is_activated", type="boolean")
     */
    protected $activated = false;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="Pim\Bundle\ConfigBundle\Entity\Channel",
     *     mappedBy="locales"
     * )
     */
    protected $channels;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->channels = new ArrayCollection();
    }

    /**
     * To string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->code;
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
     * @return \Pim\Bundle\ConfigBundle\Entity\Locale
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
     * @return \Pim\Bundle\ConfigBundle\Entity\Locale
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get fallback
     *
     * @return string
     */
    public function getFallback()
    {
        return $this->fallback;
    }

    /**
     * Set fallback
     *
     * @param string $fallback
     *
     * @return \Pim\Bundle\ConfigBundle\Entity\Locale
     */
    public function setFallback($fallback)
    {
        $this->fallback = $fallback;

        return $this;
    }

    /**
     * Is activated
     *
     * @return boolean
     */
    public function isActivated()
    {
        return $this->activated;
    }

    /**
     * Get default currency
     *
     * @return Currency
     *
     * TODO : Must be removed
     */
    public function getDefaultCurrency()
    {
        return $this->defaultCurrency;
    }

    /**
     * Set currencies
     *
     * @param Currency $currency
     *
     * @return \Pim\Bundle\ConfigBundle\Entity\Locale
     *
     * @TODO : must be removed
     */
    public function setDefaultCurrency(Currency $currency)
    {
        $this->defaultCurrency = $currency;

        return $this;
    }

    /**
     * Get channels
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * Set channels
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $channels
     *
     * @return \Pim\Bundle\ConfigBundle\Entity\Locale
     */
    public function setChannels($channels)
    {
        $this->channels = $channels;

        return $this;
    }

    /**
     * Activate the locale
     *
     * @return \Pim\Bundle\ConfigBundle\Entity\Locale
     */
    public function activate()
    {
        $this->activated = true;

        return $this;
    }

    /**
     * Deactivate the locale
     * Only if it's no more link to a channel so <= 1 because it's call before persist
     *
     * @return \Pim\Bundle\ConfigBundle\Entity\Locale
     */
    public function deactivate()
    {
        if ($this->getChannels()->count() <= 1) {
            $this->activated = false;
        }

        return $this;
    }
}
