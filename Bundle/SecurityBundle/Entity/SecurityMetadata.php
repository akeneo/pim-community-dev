<?php

namespace Oro\Bundle\SecurityBundle\Entity;

class SecurityMetadata implements \Serializable
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $group;

    /**
     * @var string
     */
    protected $className;

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $type
     * @param $className
     * @param $group
     */
    public function __construct($type = '', $className = '', $group = '')
    {
        $this->type = $type;
        $this->className = $className;
        $this->group = $group;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(
            array(
                $this->type,
                $this->className,
                $this->group
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list(
            $this->type,
            $this->className,
            $this->group
            ) = unserialize($serialized);
    }
}
