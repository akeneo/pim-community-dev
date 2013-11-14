<?php

namespace Oro\Bundle\EntityBundle\Manager;

use Symfony\Bridge\Doctrine\ManagerRegistry;
use Oro\Bundle\EntityBundle\ORM\EntityClassResolver;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityBundle\Exception\InvalidEntityException;

/**
 * @todo: THIS CLASS IS NOT FINISHED YET
 */
class EntityFieldManager
{
    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @var EntityClassResolver
     */
    protected $entityClassResolver;

    /**
     * @var ConfigProvider
     */
    protected $entityConfigProvider;

    /**
     * Constructor
     *
     * @param ManagerRegistry     $doctrine
     * @param EntityClassResolver $entityClassResolver
     * @param ConfigProvider      $entityConfigProvider
     */
    public function __construct(
        ManagerRegistry $doctrine,
        EntityClassResolver $entityClassResolver,
        ConfigProvider $entityConfigProvider
    ) {
        $this->doctrine             = $doctrine;
        $this->entityConfigProvider = $entityConfigProvider;
        $this->entityClassResolver  = $entityClassResolver;
    }

    /**
     * Returns fields for the given entity
     *
     * @param string $entityName    Entity name. Can be full class name or short form: Bundle:Entity.
     * @param bool   $withRelations Indicates whether fields of related entities should be returned as well.
     * @return array of
     *               {
     *                  'name'              - entity full class name
     *                  'label'             - entity label
     *                  'icon'              - an icon associated with entity
     *                  'fields' array of
     *                      {
     *                          'name'      - field name
     *                          'type'      - field type
     *                          'label'     - field label
     *                      }
     *               }
     */
    public function getFields($entityName, $withRelations)
    {
        $result = array();
        $this->fillFields($result, $this->entityClassResolver->getEntityClass($entityName), $withRelations);

        $this->sortByLabel($result);

        return $result;
    }

    /**
     * @param array  $result
     * @param string $className
     * @param bool   $withRelations
     * @throws InvalidEntityException
     */
    protected function fillFields(array &$result, $className, $withRelations)
    {
        if (!$this->entityConfigProvider->hasConfig($className)) {
            // skip non configurable entities
            return;
        }

        $manager = null;
        try {
            $manager = $this->doctrine->getManagerForClass($className);
        } catch (\ReflectionException $ex) {
            // ignore not found exception
        }
        if (!$manager) {
            throw new InvalidEntityException(sprintf('The "%s" entity was not found.', $className));
        }

        $metadata = $manager->getClassMetadata($className);
        $fields   = array();
        foreach ($metadata->getFieldNames() as $fieldName) {
            $fields[] = array(
                'name'  => $fieldName,
                'type'  => $metadata->getTypeOfField($fieldName),
                'label' => $this->getFieldLabel($className, $fieldName)
            );
        }
        if ($withRelations) {
            foreach ($metadata->getAssociationNames() as $associationNames) {
            }
        }

        $this->sortByLabel($fields);

        $config = $this->entityConfigProvider->getConfig($className);
        $result[] = array(
            'name'   => $className,
            'label'  => $config->get('label'),
            'icon'   => $config->get('icon'),
            'fields' => $fields
        );
    }

    /**
     * @param string $className
     * @param string $fieldName
     * @return string
     */
    protected function getFieldLabel($className, $fieldName)
    {
        if ($this->entityConfigProvider->hasConfig($className, $fieldName)) {
            return $this->entityConfigProvider->getConfig($className, $fieldName)->get('label');
        }

        return $fieldName;
    }

    /**
     * Sorts the given associative array by 'label' attribute
     *
     * @param array $items
     */
    protected function sortByLabel(array &$items)
    {
        uasort(
            $items,
            function ($a, $b) {
                return strcmp($a['label'], $b['label']);
            }
        );
    }
}
