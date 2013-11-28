<?php

namespace Oro\Bundle\SearchBundle\Engine;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractMapper
{
    /**
     * @var array
     */
    protected $mappingConfig;

    /**
     * @var ContainerInterface
     */
    protected $container;

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
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        return $propertyAccessor->getValue($objectOrArray, $fieldName);
    }

    /**
     * Get mapping parameter for entity
     *
     * @param string $entity
     * @param string $parameter
     * @param mixed  $defaultValue
     *
     * @return mixed
     */
    public function getEntityMapParameter($entity, $parameter, $defaultValue = false)
    {
        if ($this->getEntityConfig($entity)) {
            $entityConfig = $this->getEntityConfig($entity);

            if (isset($entityConfig[$parameter])) {
                return $entityConfig[$parameter];
            }
        }

        return $defaultValue;
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
        if (isset($this->mappingConfig[(string) $entity])) {
            return $this->mappingConfig[(string) $entity];
        }

        return false;
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
        if ($value) {
            //check if field have target_fields parameter
            $targetFields = isset($fieldConfig['target_fields'])
                ? $fieldConfig['target_fields']
                : array($fieldConfig['name']);

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
        }

        return $objectData;
    }
}
