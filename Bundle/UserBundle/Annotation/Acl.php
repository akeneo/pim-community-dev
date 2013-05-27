<?php

namespace Oro\Bundle\UserBundle\Annotation;

/**
 * @Annotation
 * @Target({"METHOD", "CLASS"})
 */
class Acl
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
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $parent = null;

    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $method;

    public function __construct(array $data)
    {
        $aclId = $data['id'];
        if (preg_match('/\s/', $aclId) > 0) {
            throw new \RuntimeException('ACL Id can\'t contain blank spaces');
        }
        $this->setId($aclId);
        $this->setName($data['name']);
        $this->setDescription($data['description']);
        if (isset($data['parent'])) {
            $this->setParent($data['parent']);
        }
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return bool|string
     */
    public function getParent()
    {
        if ($this->parent) {
            return $this->parent;
        }

        return false;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @param string $parent
     */
    public function setParent($parent)
    {
        if ($parent) {
            $this->parent = $parent;
        } else {
            $this->parent = false;
        }
    }

    /**
     * @param string $className
     */
    public function setClass($className)
    {
        $this->class = $className;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $methodName
     */
    public function setMethod($methodName)
    {
        $this->method = $methodName;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }
}
