<?php
namespace Bap\Bundle\FlexibleEntityBundle\Model;

/**
 * Abstract entity value, independent of storage
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
abstract class EntityAttributeValue
{

    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var EntityAttribute $attribute
     */
    protected $attribute;

    /**
     * @var mixed $data
     */
    protected $data;

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
     * Set data
     *
     * @param  string               $data
     * @return EntityAttributeValue
     */
     public function setData($data)
     {
         $this->data = $data;

         return $this;
     }

    /**
     * Get data
     *
     * @return string
     */
     public function getData()
     {
         return $this->data;
     }

    /**
     * Set attribute
     *
     * @param  EntityAttribute      $attribute
     * @return EntityAttributeValue
     */
    public function setAttribute(EntityAttribute $attribute = null)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Get attribute
     *
     * @return EntityAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

}
