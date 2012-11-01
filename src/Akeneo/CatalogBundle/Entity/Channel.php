<?php
namespace Akeneo\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Catalog channel, aims to define scopes
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="AkeneoCatalog_Channel")
 * @ORM\Entity
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
     * @var string $localeCode
     * @ORM\Column(name="code", type="string", length=255, unique=true)
     */
    protected $code;

    /**
     * @var string $defaultLocale
     * @ORM\Column(name="default_locale", type="string")
     */
    protected $defaultLocale = 'fr_FR'; // TODO

    /**
     * @var $locales
     *
     * @ORM\OneToMany(targetEntity="ChannelLocale", mappedBy="channel", cascade={"persist", "remove"})
     */
    protected $locales;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->locales = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @param integer $id
     * @return Channel
     */
    public function setId($id)
    {
        return $this->id = $id;

        return $this;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return Channel
     */
    public function setCode($code)
    {
        $this->code = $code;

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
     * Set defaultLocale
     *
     * @param string $defaultLocale
     * @return Channel
     */
    public function setDefaultLocale($defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;

        return $this;
    }

    /**
     * Get defaultLocale
     *
     * @return string
     */
    public function getDefaultLocale()
    {
        return $this->defaultLocale;
    }

    /**
     * Add locales
     *
     * @param Akeneo\CatalogBundle\Entity\ChannelLocale $locales
     * @return Channel
     */
    public function addLocale(\Akeneo\CatalogBundle\Entity\ChannelLocale $locale)
    {
        $locale->setChannel($this);
        $this->locales[] = $locale;
        return $this;
    }

    /**
     * Remove locales
     *
     * @param Akeneo\CatalogBundle\Entity\ChannelLocale $locales
     */
    public function removeLocale(\Akeneo\CatalogBundle\Entity\ChannelLocale $locales)
    {
        $this->locales->removeElement($locales);
    }

    /**
     * Get locales
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getLocales()
    {
        return $this->locales;
    }
}