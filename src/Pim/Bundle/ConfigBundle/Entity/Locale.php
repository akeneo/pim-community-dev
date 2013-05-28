<?php
namespace Pim\Bundle\ConfigBundle\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Locale\Locale as SfLocale;

/**
 * Locale entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="pim_locale")
 * @ORM\Entity(repositoryClass="Pim\Bundle\ConfigBundle\Entity\Repository\LocaleRepository")
 * @UniqueEntity("code")
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
     * @ORM\ManyToOne(targetEntity="Currency")
     * @ORM\JoinColumn(name="default_currency_id", referencedColumnName="id")
     */
    protected $defaultCurrency;

    /**
     * @var boolean $activated
     *
     * @ORM\Column(name="is_activate", type="boolean")
     */
    protected $activated;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->activated = true;
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
     * Get activated
     *
     * @return boolean
     */
    public function getActivated()
    {
        return $this->activated;
    }

    /**
     * Set activated
     *
     * @param boolean $activated
     *
     * @return \Pim\Bundle\ConfigBundle\Entity\Locale
     */
    public function setActivated($activated)
    {
        $this->activated = $activated;

        return $this;
    }

    /**
     * Get default currency
     *
     * @return Currency
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
     */
    public function setDefaultCurrency(Currency $currency)
    {
        $this->defaultCurrency = $currency;

        return $this;
    }

    /**
     * Get displayed locale from locale code
     *
     * @param string $locale
     *
     * @return string
     */
    public function fromLocale($locale)
    {
        $countries = SfLocale::getDisplayLanguages($locale);

        return isset($countries[$this->code]) ? $countries[$this->code] : $this->code;
    }
}
