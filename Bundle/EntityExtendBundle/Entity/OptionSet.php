<?php

namespace Oro\Bundle\EntityExtendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="oro_extend_optionset")
 * @ORM\HasLifecycleCallbacks
 */
class OptionSet implements \ArrayAccess
{
    const ENTITY_NAME = 'OroEntityExtendBundle:OptionSet';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel", cascade={"persist"})
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="field_id", referencedColumnName="id")
     * })
     */
    protected $field_id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $label;

    /**
     * @var integer
     * @ORM\Column(type="smallint", length=4)
     */
    protected $priority;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $is_default;

    /**
     * @param string|null $label
     */
    public function __construct($label = null)
    {
        $this->label   = $label;
        $this->default = false;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $field_id
     * @return $this
     */
    public function setFieldId($field_id)
    {
        $this->field_id = $field_id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFieldId()
    {
        return $this->field_id;
    }

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param int $priority
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param boolean $default
     * @return $this
     */
    public function setIsDefault($default)
    {
        $this->is_default = $default;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsDefault()
    {
        return $this->is_default;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        var_dump(1, $offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        var_dump(2, $offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        var_dump(3, $offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        var_dump(4, $offset);
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        return [
            'id'       => $this->getId(),
            'field_id' => $this->getFieldId(),
            'label'    => $this->getLabel(),
            'priority' => $this->getPriority(),
            'default'  => $this->getDefault()
        ];
    }

    public function __toString()
    {
        return 'string';
    }
}
