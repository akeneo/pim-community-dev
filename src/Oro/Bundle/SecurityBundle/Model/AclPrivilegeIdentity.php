<?php

namespace Oro\Bundle\SecurityBundle\Model;

class AclPrivilegeIdentity
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * Constructor
     *
     * @param string|null $id
     * @param string|null $name
     */
    public function __construct($id = null, $name = null)
    {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param  string               $id
     * @return AclPrivilegeIdentity
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param  string               $name
     * @return AclPrivilegeIdentity
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
}
