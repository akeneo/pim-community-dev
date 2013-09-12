<?php

namespace Oro\Bundle\SecurityBundle\Metadata;

use Oro\Bundle\SecurityBundle\Annotation\Acl as AclAnnotation;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor as AclAnnotationAncestor;

class AclAnnotationStorage
{
    /**
     * @var AclAnnotation[]
     *   key = annotation id
     *   value = annotation object
     */
    private $annotations = array();

    /**
     * @var string[]
     *   key = a binding key (class!method or class)
     *   value = annotation id
     */
    private $bindings = array();

    /**
     * Gets an annotation by its id
     *
     * @param string $id
     * @return AclAnnotation|null
     * @throws \InvalidArgumentException
     */
    public function findById($id)
    {
        if (empty($id)) {
            throw new \InvalidArgumentException('$id must not be empty.');
        }

        return isset($this->annotations[$id])
            ? $this->annotations[$id]
            : null;
    }

    /**
     * Gets an annotation bound to the given class/method
     *
     * @param string $class
     * @param string|null $method
     * @return AclAnnotation|null
     * @throws \InvalidArgumentException
     */
    public function find($class, $method = null)
    {
        if (empty($class)) {
            throw new \InvalidArgumentException('$class must not be empty.');
        }

        $key = empty($method) ? $class : $class . '!' . $method;
        if (!isset($this->bindings[$key])) {
            return null;
        }

        $id = $this->bindings[$key];

        return isset($this->annotations[$id])
            ? $this->annotations[$id]
            : null;
    }

    /**
     * Gets annotations
     *
     * @param string|null $type The annotation type
     * @return AclAnnotation[]
     */
    public function getAnnotations($type = null)
    {
        if ($type === null) {
            return array_values($this->annotations);
        }

        $result = array();
        foreach ($this->annotations as $annotation) {
            if ($annotation->getType() === $type) {
                $result[] = $annotation;
            }
        }
        return $result;
    }

    /**
     * Adds an annotation
     *
     * @param AclAnnotation $annotation
     * @param string|null $class
     * @param string|null $method
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function add(AclAnnotation $annotation, $class = null, $method = null)
    {
        $id = $annotation->getId();
        $this->annotations[$id] = $annotation;
        if ($class !== null) {
            $this->addBinding($id, $class, $method);
        }
    }

    /**
     * Adds an annotation ancestor
     *
     * @param AclAnnotationAncestor $ancestor
     * @param string|null $class
     * @param string|null $method
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function addAncestor(AclAnnotationAncestor $ancestor, $class = null, $method = null)
    {
        if ($class !== null) {
            $this->addBinding($ancestor->getId(), $class, $method);
        }
    }

    /**
     * Adds an annotation binding
     *
     * @param string $id
     * @param string $class
     * @param string|null $method
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function addBinding($id, $class, $method = null)
    {
        if (empty($class)) {
            throw new \InvalidArgumentException('$class must not be empty.');
        }

        $key = empty($method) ? $class : $class . '!' . $method;
        if (isset($this->bindings[$key])) {
            throw new \RuntimeException(
                sprintf(
                    'Duplicate binding for "%s". New Id: %s. Existing Id: %s',
                    empty($method) ? $class : $class . '::' . $method,
                    $id,
                    $this->bindings[$key]
                )
            );
        }
        $this->bindings[$key] = $id;
    }
}
