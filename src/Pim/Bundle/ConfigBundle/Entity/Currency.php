<?php
namespace Pim\Bundle\ConfigBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;

/**
 * Currency entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="pim_currency")
 * @ORM\Entity
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
     * @ORM\Column(name="is_activate", type="boolean")
     */
    protected $activated;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Language", mappedBy="currencies")
     */
    protected $languages;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->activated = true;
        $this->languages = new ArrayCollection();
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
     * @return \Pim\Bundle\ConfigBundle\Entity\Currency
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
     * @return \Pim\Bundle\ConfigBundle\Entity\Currency
     */
    public function setCode($code)
    {
        $this->code = $code;

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
     * @return \Pim\Bundle\ConfigBundle\Entity\Currency
     */
    public function setActivated($activated)
    {
        $this->activated = $activated;

        return $this;
    }

    /**
     * Get languages
     *
     * @return \Pim\Bundle\ConfigBundle\Entity\ArrayCollection
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * Set languages
     *
     * @param array $languages
     *
     * @return \Pim\Bundle\ConfigBundle\Entity\Currency
     */
    public function setLanguages($languages = array())
    {
        $this->languages = new ArrayCollection($languages);

        return $this;
    }
}
