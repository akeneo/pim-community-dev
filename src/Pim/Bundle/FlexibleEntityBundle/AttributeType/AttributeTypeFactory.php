<?php

namespace Pim\Bundle\FlexibleEntityBundle\AttributeType;

/**
 * The attribute type factory
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeTypeFactory
{
    /**
     * Types alias to reference
     *
     * @var \ArrayAccess
     */
    protected $types;

    /**
     * Entity to aliases
     *
     * @var \ArrayAccess
     */
    protected $entityToAliases;

    /**
     * @param array $types
     */
    public function __construct(array $types = array())
    {
        $this->types = $types;
    }

    /**
     * Get type aliases
     *
     * @param string $entity
     *
     * @return array
     */
    public function getAttributeTypes($entity)
    {
        if (!$this->entityToAliases) {
            foreach ($this->types as $alias => $properties) {
                $entity = $properties['entity'];
                if (!isset($this->entityToAliases[$entity])) {
                    $this->entityToAliases[$entity]= array();
                }
                $this->entityToAliases[$entity][]= $alias;
            }
        }

        return (isset($this->entityToAliases[$entity])) ? $this->entityToAliases[$entity] : array();
    }

    /**
     * Add a type
     *
     * @param string                 $typeAlias     type alias
     * @param AttributeTypeInterface $attributeType type
     *
     * @return AttributeTypeFactory
     */
    public function addType($typeAlias, AttributeTypeInterface $attributeType)
    {
        if (!$attributeType instanceof AttributeTypeInterface) {
            throw new \RunTimeException(sprintf('The service "%s" must be a "AttributeTypeInterface"', $typeAlias));
        }

        $this->types[$typeAlias] = $attributeType;

        return $this;
    }

    /**
     * Get the attribute type service
     *
     * @param string $typeAlias alias
     * @param string $entity    entity FQCN
     *
     * @return AttributeTypeInterface
     * @throws \RunTimeException
     */
    public function get($typeAlias, $entity)
    {
        if (!$typeAlias) {
            throw new \RunTimeException(sprintf('The type %s is not defined', $typeAlias));
        }

        /** @var $attributeType AttributeTypeInterface */
        $attributeType = isset($this->types[$typeAlias]['type']) ? $this->types[$typeAlias]['type'] : false;

        if (!$attributeType) {
            throw new \RunTimeException(sprintf('No attached service to type named "%s"', $typeAlias));
        }

        if ($entity !== $this->types[$typeAlias]['entity']) {
            throw new \RunTimeException(
                sprintf('Attribute "%s" type is not useable for the flexible entity "%s"', $typeAlias, $entity)
            );
        }

        return $attributeType;
    }
}
