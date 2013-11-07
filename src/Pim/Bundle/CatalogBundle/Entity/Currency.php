<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation\ExclusionPolicy;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

/**
 * Currency entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="pim_catalog_currency")
 * @ORM\Entity(repositoryClass="Pim\Bundle\CatalogBundle\Entity\Repository\CurrencyRepository")
 * @UniqueEntity("code")
 * @Config(
 *  defaultValues={
 *      "entity"={"label"="Currency", "plural_label"="Currencies"},
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      }
 *  }
 * )
 *
 * @ExclusionPolicy("all")
 */
class Currency
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
     * @ORM\Column(name="code", type="string", length=3, unique=true)
     */
    protected $code;

    /**
     * @var boolean $activated
     *
     * @ORM\Column(name="is_activated", type="boolean")
     */
    protected $activated;

    /**
     * @var ArrayCollection $locales
     *
     * @ORM\OneToMany(targetEntity="Locale", mappedBy="defaultCurrency")
     */
    protected $locales;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->activated = true;
        $this->locales = new ArrayCollection();
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
     * @return Currency
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
     * @return Currency
     */
    public function setCode($code)
    {
        $this->code = $code;

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
     * Toggle activation
     */
    public function toggleActivation()
    {
        $this->activated = !$this->activated;
    }

    /**
     * Set activated
     *
     * @param boolean $activated
     *
     * @return Currency
     */
    public function setActivated($activated)
    {
        $this->activated = $activated;

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
     * Set locales
     *
     * @param array $locales
     *
     * @return Currency
     */
    public function setLocales($locales = array())
    {
        $this->locales = new ArrayCollection($locales);

        return $this;
    }
}
