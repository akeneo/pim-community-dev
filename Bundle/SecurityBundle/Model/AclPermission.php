<?php

namespace Oro\Bundle\SecurityBundle\Model;

class AclPermission
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var int Can be any AccessLevel::*_LEVEL
     */
    private $accessLevel;

    /**
     * Constructor
     *
     * @param string|null $name
     * @param int|null    $accessLevel Can be any AccessLevel::*_LEVEL
     */
    public function __construct($name = null, $accessLevel = null)
    {
        $this->name = $name;
        $this->accessLevel = $accessLevel;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param  string        $name
     * @return AclPermission
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Can be any AccessLevel::*_LEVEL
     *
     * @return int
     */
    public function getAccessLevel()
    {
        return $this->accessLevel;
    }

    /**
     * @param  int           $accessLevel Can be any AccessLevel::*_LEVEL
     * @return AclPermission
     */
    public function setAccessLevel($accessLevel)
    {
        $this->accessLevel = $accessLevel;

        return $this;
    }
}
