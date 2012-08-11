<?php

namespace Strixos\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Strixos\CatalogBundle\Entity\Attribute
 *
 * @ORM\Table(name="StrixosCatalog_Attribute")
 * @ORM\Entity
 */
class Attribute
{
    const BACKEND_TYPE_INT      = 1;
    const BACKEND_TYPE_VARCHAR  = 2;
    const BACKEND_TYPE_TEXT     = 3;
    const BACKEND_TYPE_DATETIME = 4;
    const BACKEND_TYPE_DECIMAL  = 5;

    /*
     string
    int
    float
    date
    timestamp
    boolean
    file

    TODO: how to deal with mult-select
    */


    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $code
     *
     * @ORM\Column(name="code", type="string", length=255, unique=true)
     */
    private $code;

    /**
     * @var string $type
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

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
     * Set code
     *
     * @param string $code
     * @return Attribute
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
     * Set type
     *
     * @param string $type
     * @return Attribute
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}