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
    public function __construct(?string $id = null, ?string $name = null)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param  string               $id
     */
    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param  string               $name
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
