<?php

namespace Strixos\CatalogBundle\Entity;

use Strixos\CoreBundle\Model\AbstractModel;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Nicolas Dupont @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Strixos\CatalogBundle\Entity\Option
 *
 * @ORM\Table(name="StrixosCatalog_Option")
 * @ORM\Entity
 */
class Option extends AbstractModel
{

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
    * @ORM\ManyToOne(targetEntity="Attribute")
    */
    protected $attribute;

    /**
     * @var string $code
     *
     * @ORM\Column(name="value", type="string", length=255)
     */
    protected $value;

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
     * Set value
     *
     * @param string $value
     * @return Option
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set attribute
     *
     * @param Strixos\CatalogBundle\Entity\Attribute $attribute
     * @return Option
     */
    public function setAttribute(\Strixos\CatalogBundle\Entity\Attribute $attribute = null)
    {
        $this->attribute = $attribute;
    
        return $this;
    }

    /**
     * Get attribute
     *
     * @return Strixos\CatalogBundle\Entity\Attribute 
     */
    public function getAttribute()
    {
        return $this->attribute;
    }
}