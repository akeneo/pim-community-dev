<?php
namespace Bap\Bundle\FlexibleEntityBundle\Model;

/**
 * Abstract entity value, independent of storage
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
abstract class EntityValue
{

    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var EntityField $field
     */
    protected $field;

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
     * @param string $data
     * @return EntityValue
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
     * Set field
     *
     * @param EntityField $field
     * @return EntityValue
     */
    public function setField(EntityField $field = null)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Get field
     *
     * @return EntityField
     */
    public function getField()
    {
        return $this->field;
    }

}