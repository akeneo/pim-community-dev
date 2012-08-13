<?php

namespace Strixos\CatalogBundle\Entity;

use Strixos\CoreBundle\Model\AbstractModel;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Nicolas Dupont @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Strixos\CatalogBundle\Entity\Attribute
 *
 * @ORM\Table(name="StrixosCatalog_Attribute")
 * @ORM\Entity
 */
class Attribute extends AbstractModel
{

    const FRONTEND_TYPE_INPUT       = 'input';
    const FRONTEND_TYPE_SELECT      = 'select';
    /* others ... */

    const BACKEND_TYPE_VARCHAR  = 'string'; // TODO same in mongo ?
    const BACKEND_TYPE_TEXT     = 'string';
    const BACKEND_TYPE_INT      = 'int';
    const BACKEND_TYPE_DECIMAL  = 'string'; // TODO pre-format ?
    const BACKEND_TYPE_DATE     = 'date';
    const BACKEND_TYPE_DATETIME = 'datetime';
    const BACKEND_TYPE_BOOLEAN  = 'boolean';
    const BACKEND_TYPE_FILE     = 'file';
    /* others ... */

    /*
     * | attribute_code
     * | backend_type
     * | frontend_input
     * | source_model
     * | is_unique
     * | is_required
     * | default_value
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
     * @var integer $type
     *
     * @ORM\Column(name="is_required", type="boolean")
     */
    private $isRequired;

    /**
     * @var integer $type
     *
     * @ORM\Column(name="is_unique", type="boolean")
     */
    private $isUnique;

    /**
     * @var string $input
     *
     * @ORM\Column(name="frontend_input", type="string", length=255)
     */
    private $input;

    /**
     * @var string $type
     *
     * @ORM\Column(name="backend_type", type="string", length=255)
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
     * Set id TODO: to works with updateaction binding ...
     *
     */
    public function setId($id)
    {
        $this->id = $id;
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

    /**
     * Set isRequired
     *
     * @param boolean $isRequired
     * @return Attribute
     */
    public function setIsRequired($isRequired)
    {
        $this->isRequired = $isRequired;

        return $this;
    }

    /**
     * Get isRequired
     *
     * @return boolean
     */
    public function getIsRequired()
    {
        return $this->isRequired;
    }

    /**
     * Set isUnique
     *
     * @param boolean $isUnique
     * @return Attribute
     */
    public function setIsUnique($isUnique)
    {
        $this->isUnique = $isUnique;

        return $this;
    }

    /**
     * Get isUnique
     *
     * @return boolean
     */
    public function getIsUnique()
    {
        return $this->isUnique;
    }

    /**
     * Set input
     *
     * @param string $input
     * @return Attribute
     */
    public function setInput($input)
    {
        $this->input = $input;

        return $this;
    }

    /**
     * Get input
     *
     * @return string
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Return backend type option as associative array
     * @return array
     */
    public static function getBackendTypeOptions()
    {
        // TODO: deal with translations
        return array(
            self::BACKEND_TYPE_VARCHAR  => self::BACKEND_TYPE_VARCHAR,
            self::BACKEND_TYPE_TEXT     => self::BACKEND_TYPE_TEXT,
            self::BACKEND_TYPE_INT      => self::BACKEND_TYPE_INT,
            self::BACKEND_TYPE_DECIMAL  => self::BACKEND_TYPE_DECIMAL,
            self::BACKEND_TYPE_DATE     => self::BACKEND_TYPE_DATE,
            self::BACKEND_TYPE_DATETIME => self::BACKEND_TYPE_DATETIME,
            self::BACKEND_TYPE_BOOLEAN  => self::BACKEND_TYPE_BOOLEAN,
            self::BACKEND_TYPE_FILE     => self::BACKEND_TYPE_FILE,
        );
    }

    /**
    * Return frontend input option as associative array
    * @return array
    */
    public static function getFrontendInputOptions()
    {
        // TODO: deal with translations
        return array(
            self::FRONTEND_TYPE_INPUT  => self::FRONTEND_TYPE_INPUT,
            self::FRONTEND_TYPE_SELECT => self::FRONTEND_TYPE_SELECT
        );
    }

}