<?php
namespace Bap\Bundle\FlexibleEntityBundle\Model;

/**
 * Abstract entity type, independent of storage
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
abstract class EntityType
{

    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var string $code
     */
    protected $code;

    /**
    * @var string $title
    */
    protected $title;

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
     * @return EntityType
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }


    /**
     * Set title
     *
     * @param string $title
     * @return EntityType
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

}