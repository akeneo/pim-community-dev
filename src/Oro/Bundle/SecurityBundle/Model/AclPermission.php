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
    public function __construct(?string $name = null, ?int $accessLevel = null)
    {
        $this->name = $name;
        $this->accessLevel = $accessLevel;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param  string        $name
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Can be any AccessLevel::*_LEVEL
     */
    public function getAccessLevel(): int
    {
        return $this->accessLevel;
    }

    /**
     * @param  int           $accessLevel Can be any AccessLevel::*_LEVEL
     */
    public function setAccessLevel(int $accessLevel): self
    {
        $this->accessLevel = $accessLevel;

        return $this;
    }
}
