<?php
namespace Bap\FlexibleEntityBundle\Model;

/**
 * Allow to easily create various kind of entity, type, group, field
 * The implented class can be injected when declare the factory service
 *
 * TODO use get in place of build to get or create ?
 *
 * @author    Nicolas Dupont @ Strixos
 * @copyright Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
use Strixos\CatalogEavBundle\Entity\Field;

class Factory
{

    /**
     * @var Entity $_entityClass
     */
    protected $_entityClass;

    /**
    * @var EntityType $_entityTypeClass
    */
    protected $_typeClass;

    /**
    * @var EntityFieldGroup $_groupClass
    */
    protected $_groupClass;

    /**
    * @var Field $_fieldClass
    */
    protected $_fieldClass;

   /**
     * Aims to inject implementation
     *
     * @param Entity $entityClass
     * @param EntityType $typeClass
     * @param EntityFieldGroup $groupClass
     * @param EntityFieldGroup $fieldClass
     */
    public function __construct($entityClass, $typeClass, $groupClass, $fieldClass)
    {
        $this->_entityClass = $entityClass;
        $this->_typeClass   = $typeClass;
        $this->_groupClass  = $groupClass;
        $this->_fieldClass  = $fieldClass;
    }

    /**
     * Build an entity
     * @param EntityType $type
     * @return Entity
     */
    public function buildEntity($type)
    {
        $entity = new $this->_entityClass();
        $entity->setType($type);
        return $entity;
    }

    /**
     * Build an entity type
     * @param string $code
     * @return EntityType
     */
    public function buildType($code)
    {
        $type = new $this->_typeClass;
        $type->setCode($code);
        return $type;
    }

    /**
     * Build a group
     * @return EntityFieldGroup
     */
    public function buildGroup($code, $type)
    {
        $group = new $this->_groupClass;
        $group->setCode($code);
        $group->setType($type);
        return $group;
    }

    /**
     * Build a field
     * @param string $code
     * @return Field
     */
    public function buildField($code)
    {
        $field = new $this->_fieldClass;
        $field->setCode($code);
        return $field;
    }

}