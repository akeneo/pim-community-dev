<?php
namespace Oro\Bundle\SearchBundle\Engine;

use Symfony\Component\Form\Util\PropertyPath;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;

class ObjectMapper
{

    /**
     * @var array
     */
    private $mappingConfig;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container, $mappingConfig)
    {
        $this->mappingConfig = $mappingConfig;
        $this->container     = $container;
    }

    /**
     * @return array
     */
    public function getMappingConfig()
    {
        return $this->mappingConfig;
    }

    /**
     * Get array with entity aliases
     *
     * @return array
     */
    public function getEntitiesLabels()
    {
        $entities = array();

        foreach ($this->mappingConfig as $class => $mappingEntity) {
            $entities[] = array(
                'alias' => isset($mappingEntity['alias']) ? $mappingEntity['alias'] : '',
                'class' => $class,
            );
        }

        return $entities;
    }

    /**
     * Get mapping parameter for entity
     *
     * @param string $entity
     * @param string $parameter
     *
     * @return bool|array
     */
    public function getEntityMapParameter($entity, $parameter)
    {
        if ($this->getEntityConfig($entity)) {
            $entityConfig = $this->getEntityConfig($entity);

            if (isset($entityConfig[$parameter])) {
                return $entityConfig[$parameter];
            }
        }

        return false;
    }

    /**
     * Get search entities list
     *
     * @return array
     */
    public function getEntities()
    {
        return array_keys($this->mappingConfig);
    }

    /**
     * Get mapping config for entity
     *
     * @param string $entity
     *
     * @return bool|array
     */
    public function getEntityConfig($entity)
    {
        if (isset($this->mappingConfig[(string)$entity])) {
            return $this->mappingConfig[(string)$entity];
        }

        return false;
    }

    /**
     * Map object data for index
     *
     * @param object $object
     *
     * @return array
     */
    public function mapObject($object)
    {
        $mappingConfig = $this->mappingConfig;
        $objectData = array();

        if (is_object($object) && isset($mappingConfig[get_class($object)])) {
            $config = $mappingConfig[get_class($object)];
            if (isset($config['alias'])) {
                $alias = $config['alias'];
            } else {
                $alias = get_class($object);
            }
            foreach ($config['fields'] as $field) {
                // check field relation type and set it to null if field doesn't have relations
                if (!isset($field['relation_type'])) {
                    $field['relation_type'] = 'none';
                }

                $value = $this->getFieldValue($object, $field['name']);

                switch ($field['relation_type']) {
                    case Indexer::RELATION_ONE_TO_ONE:
                    case Indexer::RELATION_MANY_TO_ONE:
                        $objectData = $this->setRelatedFields(
                            $alias,
                            $objectData,
                            $field['relation_fields'],
                            $value,
                            $field['name']
                        );

                        break;
                    case Indexer::RELATION_MANY_TO_MANY:
                    case Indexer::RELATION_ONE_TO_MANY:
                        foreach ($value as $relationObject) {
                            $objectData = $this->setRelatedFields(
                                $alias,
                                $objectData,
                                $field['relation_fields'],
                                $relationObject,
                                $field['name']
                            );
                        }

                        break;
                    default:
                        if ($value) {
                            $objectData = $this->setDataValue($alias, $objectData, $field, $value);
                        }
                }
            }
            if (isset($config['flexible_manager'])) {
                $objectData = $this->setFlexibleFields($alias, $object, $objectData, $config['flexible_manager']);
            }
        }

        return $objectData;
    }

    /**
     * Get object field value
     *
     * @param object|array $objectOrArray
     * @param string       $fieldName
     *
     * @return mixed
     */
    public function getFieldValue($objectOrArray, $fieldName)
    {
        $propertyPath = new PropertyPath($fieldName);

        return $propertyPath->getValue($objectOrArray);
    }

    /**
     * Set related fields values
     *
     * @param string $alias
     * @param array  $objectData
     * @param array  $relationFields
     * @param object $relationObject
     * @param string $parentName
     *
     * @return array
     */
    protected function setRelatedFields($alias, $objectData, $relationFields, $relationObject, $parentName)
    {
        foreach ($relationFields as $relationObjectField) {
            $value = $this->getFieldValue($relationObject, $relationObjectField['name']);
            if ($value) {
                $relationObjectField['name'] = $parentName;
                $objectData = $this->setDataValue(
                    $alias,
                    $objectData,
                    $relationObjectField,
                    $value
                );
            }
        }

        return $objectData;
    }

    /**
     * Set value for meta fields by type
     *
     * @param string $alias
     * @param array  $objectData
     * @param array  $fieldConfig
     * @param mixed  $value
     *
     * @return array
     */
    protected function setDataValue($alias, $objectData, $fieldConfig, $value)
    {
        //check if field have target_fields parameter
        if (isset($fieldConfig['target_fields']) && count($fieldConfig['target_fields'])) {
            $targetFields = $fieldConfig['target_fields'];
        } else {
            $targetFields = array($fieldConfig['name']);
        }

        if ($fieldConfig['target_type'] != 'text') {
            foreach ($targetFields as $targetField) {
                $objectData[$fieldConfig['target_type']][$targetField] = $value;
            }

        } else {
            foreach ($targetFields as $targetField) {
                if (!isset($objectData[$fieldConfig['target_type']][$targetField])) {
                    $objectData[$fieldConfig['target_type']][$targetField] = '';
                }
                $objectData[$fieldConfig['target_type']][$targetField] .= $value . ' ';
            }
            if (!isset($objectData[$fieldConfig['target_type']][Indexer::TEXT_ALL_DATA_FIELD])) {
                $objectData[$fieldConfig['target_type']][Indexer::TEXT_ALL_DATA_FIELD] = '';
            }
            $objectData[$fieldConfig['target_type']][Indexer::TEXT_ALL_DATA_FIELD] .= $value . ' ';
        }

        return $objectData;
    }

    /**
     * Map Flexible entity fields
     *
     * @param string $alias
     * @param        $object
     * @param array  $objectData
     * @param string $managerName
     *
     * @return array
     */
    protected function setFlexibleFields($alias, $object, $objectData, $managerName)
    {
        /** @var $flexibleManager \Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager */
        $flexibleManager = $this->container->get($managerName);
        if ($flexibleManager) {
            $attributes = $flexibleManager->getAttributeRepository()
                ->findBy(array('entityType' => $flexibleManager->getFlexibleName()));
            if (count($attributes)) {
                /** @var $attribute \Oro\Bundle\FlexibleEntityBundle\Entity\Attribute */
                foreach ($attributes as $attribute) {
                    if ($attribute->getSearchable()) {
                        $value = $object->getValue($attribute->getCode());
                        if ($value) {
                            $attributeType = $attribute->getBackendType();

                            switch ($attributeType) {
                                case AbstractAttributeType::BACKEND_TYPE_TEXT:
                                case AbstractAttributeType::BACKEND_TYPE_VARCHAR:
                                    $objectData = $this->saveFlexibleTextData(
                                        $alias,
                                        $objectData,
                                        $attribute->getCode(),
                                        $value->__toString()
                                    );
                                    break;
                                case AbstractAttributeType::BACKEND_TYPE_DATETIME:
                                case AbstractAttributeType::BACKEND_TYPE_DATE:
                                    $objectData = $this->saveFlexibleData(
                                        $alias,
                                        $objectData,
                                        AbstractAttributeType::BACKEND_TYPE_DATETIME,
                                        $attribute->getCode(),
                                        $value->getData()
                                    );
                                    break;
                                default:
                                    $objectData = $this->saveFlexibleData(
                                        $alias,
                                        $objectData,
                                        $attributeType,
                                        $attribute->getCode(),
                                        $value->__toString()
                                    );
                            }
                        }
                    }
                }
            }
        }

        return $objectData;
    }

    /**
     * @param string $alias
     * @param array  $objectData
     * @param string $attribute
     * @param mixed  $value
     *
     * @return array
     */
    protected function saveFlexibleTextData($alias, $objectData, $attribute, $value)
    {
        if ($value !== null) {
            if (!isset($objectData[AbstractAttributeType::BACKEND_TYPE_TEXT][$attribute])) {
                $objectData[AbstractAttributeType::BACKEND_TYPE_TEXT][$attribute] = '';
            }
            $objectData[AbstractAttributeType::BACKEND_TYPE_TEXT][$attribute] .= " " . $value;
            if (!isset($objectData[AbstractAttributeType::BACKEND_TYPE_TEXT][Indexer::TEXT_ALL_DATA_FIELD])) {
                $objectData[AbstractAttributeType::BACKEND_TYPE_TEXT][Indexer::TEXT_ALL_DATA_FIELD] = '';
            }
            $objectData[AbstractAttributeType::BACKEND_TYPE_TEXT][Indexer::TEXT_ALL_DATA_FIELD] .= " " . $value;
            $objectData[AbstractAttributeType::BACKEND_TYPE_TEXT][$alias . '_' . $attribute] = $value;
        }
        return $objectData;
    }

    /**
     * @param string $alias
     * @param array  $objectData
     * @param string $attributeType
     * @param string $attribute
     * @param mixed  $value
     *
     * @return array
     */
    protected function saveFlexibleData($alias, $objectData, $attributeType, $attribute, $value)
    {
        if ($attributeType != AbstractAttributeType::BACKEND_TYPE_OPTION) {
            $objectData[$attributeType][$attribute] = $value;
        }

        return $objectData;
    }
}
