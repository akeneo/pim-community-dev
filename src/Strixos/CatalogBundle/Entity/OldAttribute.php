<?php

namespace Strixos\CatalogCatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Strixos\CatalogBundle\Entity\Attribute
 *
 * @ORM\Entity
 * @ORM\Table(name="StrixosCatalog_Attribute")
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
     * @ORM\Column(name="code", type="string", length=255)
     */
    private $code;

    /**
    * @var string $type
    *
    * @ORM\Column(name="type", type="string", length=255)
    */
    private $type;

    /**
    * Set code
    *
    * @param string $code
    */
    public function setCode($code)
    {
        $this->code = $code;
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
    */
    public function setType($type)
    {
        $this->type = $type;
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