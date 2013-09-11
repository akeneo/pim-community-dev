<?php

namespace Oro\Bundle\SecurityBundle\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class AclAncestor
{
    /**
     * @var string
     */
    private $id;

    public function __construct(array $data)
    {
        $this->setId($data["value"]);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
}
