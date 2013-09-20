<?php

namespace Oro\Bundle\EntityConfigBundle\Datagrid;

use Doctrine\ORM\Query;

use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Datagrid\ResultRecord;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Property\ActionConfigurationProperty;
use Oro\Bundle\GridBundle\Property\UrlProperty;

use Oro\Bundle\EntityConfigBundle\Config\ConfigModelManager;
use Oro\Bundle\EntityConfigBundle\Provider\PropertyConfigContainer;
use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;

class EntityFieldsDatagridManager extends BaseDatagrid
{
    /**
     * @var integer id
     */
    protected $entityId;

    /**
     * @param $id
     */
    public function setEntityId($id)
    {
        $this->entityId = $id;
    }

    /**
     * @param  EntityConfigModel $entity
     * @return array
     */
    public function getLayoutActions(EntityConfigModel $entity)
    {
        $actions = array();
        foreach ($this->configManager->getProviders() as $provider) {
            foreach ($provider->getPropertyConfig()->getLayoutActions(PropertyConfigContainer::TYPE_FIELD) as $config) {
                if (isset($config['filter'])) {
                    foreach ($config['filter'] as $key => $value) {
                        if (is_array($value)) {
                            $error = true;
                            foreach ($value as $v) {
                                if ($provider->getConfig($entity->getClassName())->get($key) == $v) {
                                    $error = false;
                                }
                            }
                            if ($error) {
                                continue 2;
                            }
                        } elseif ($provider->getConfig($entity->getClassName())->get($key) != $value) {
                            continue 2;
                        }
                    }
                }

                if (isset($config['entity_id']) && $config['entity_id'] == true) {
                    $config['args'] = array('id' => $entity->getId());
                }

                $actions[] = $config;
            }
        }

        return $actions;
    }

    /**
     * @return array
     */
    public function getRequireJsModules()
    {
        $modules = array();
        foreach ($this->configManager->getProviders() as $provider) {
            $modules = array_merge(
                $modules,
                $provider->getPropertyConfig(PropertyConfigContainer::TYPE_FIELD)->getRequireJsModules()
            );
        }

        return $modules;
    }

    /**
     * {@inheritDoc}
     */
    protected function getProperties()
    {
        $properties = array(
            new UrlProperty('update_link', $this->router, 'oro_entityconfig_field_update', array('id')),
        );

        $filters = array();
        $actions = array();

        foreach ($this->configManager->getProviders() as $provider) {
            $gridActions = $provider->getPropertyConfig()->getGridActions(PropertyConfigContainer::TYPE_FIELD);

            $this->prepareProperties($gridActions, $properties, $actions, $filters);
        }

        if (count($filters)) {
            $properties[] = new ActionConfigurationProperty(
                function (ResultRecord $record) use ($filters, $actions) {
                    if ($record->getValue('mode') == ConfigModelManager::MODE_READONLY) {
                        $actions = array_map(
                            function () {
                                return false;
                            },
                            $actions
                        );

                        $actions['update']   = false;
                        $actions['rowClick'] = false;
                    } else {
                        foreach ($filters as $action => $filter) {
                            foreach ($filter as $key => $value) {
                                if (is_array($value)) {
                                    $error = true;
                                    foreach ($value as $v) {
                                        if ($record->getValue($key) == $v) {
                                            $error = false;
                                        }
                                    }
                                    if ($error) {
                                        $actions[$action] = false;
                                        break;
                                    }
                                } else {
                                    if ($record->getValue($key) != $value) {
                                        $actions[$action] = false;
                                        break;
                                    }
                                }
                            }
                        }
                    }

                    return $actions;
                }
            );
        }

        return $properties;
    }

    /**
     * @param FieldDescriptionCollection $fieldsCollection
     */
    protected function getDynamicFields(FieldDescriptionCollection $fieldsCollection)
    {
        $fields = array();

        foreach ($this->configManager->getProviders() as $provider) {
            foreach ($provider->getPropertyConfig()->getItems(PropertyConfigContainer::TYPE_FIELD) as $code => $item) {
                if (isset($item['grid'])) {
                    $fieldObject = new FieldDescription();
                    $fieldObject->setName($code);
                    $fieldObject->setOptions(
                        array_merge(
                            $item['grid'],
                            array(
                                'expression' => 'cfv_' . $code . '.value',
                                'field_name' => $code,
                            )
                        )
                    );

                    if (isset($item['options']['priority']) && !isset($fields[$item['options']['priority']])) {
                        $fields[$item['options']['priority']] = $fieldObject;
                    } else {
                        $fields[] = $fieldObject;
                    }
                }
            }
        }

        ksort($fields);
        foreach ($fields as $field) {
            $fieldsCollection->add($field);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldObjectClassName = new FieldDescription();
        $fieldObjectClassName->setName('className');
        $fieldObjectClassName->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'ClassName',
                'field_name'  => 'className',
                'show_column' => false,
                'expression'  => 'ce.className'
            )
        );
        $fieldsCollection->add($fieldObjectClassName);

        $fieldCode = new FieldDescription();
        $fieldCode->setName('fieldName');
        $fieldCode->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'Name',
                'field_name'  => 'fieldName',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => false,
                'show_filter' => false,
            )
        );
        $fieldsCollection->add($fieldCode);

        $fieldType = new FieldDescription();
        $fieldType->setName('type');
        $fieldType->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'Data Type',
                'field_name'  => 'type',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => false,
                'show_filter' => false,
            )
        );
        $fieldsCollection->add($fieldType);

        $this->getDynamicFields($fieldsCollection);
    }

    /**
     * {@inheritDoc}
     */
    protected function getRowActions()
    {
        $clickAction = array(
            'name'         => 'rowClick',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label'         => 'Edit',
                'link'          => 'update_link',
                'runOnRowClick' => true,
            )
        );

        $updateAction = array(
            'name'         => 'update',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label' => 'Edit',
                'icon'  => 'edit',
                'link'  => 'update_link',
            )
        );

        $actions = array($clickAction, $updateAction);

        $this->prepareRowActions($actions, PropertyConfigContainer::TYPE_FIELD);

        return $actions;
    }

    /**
     * @return ProxyQueryInterface
     */
    protected function createQuery()
    {
        /** @var ProxyQueryInterface|Query $query */
        $query = parent::createQuery();
        $query->where('cf.mode <> :mode');
        $query->setParameter('mode', ConfigModelManager::MODE_HIDDEN);
        $query->innerJoin('cf.entity', 'ce', 'WITH', 'ce.id=' . $this->entityId);
        $query->addSelect('ce.id as entity_id', true);

        foreach ($this->configManager->getProviders() as $provider) {
            foreach ($provider->getPropertyConfig()->getItems(PropertyConfigContainer::TYPE_FIELD) as $code => $item) {
                //$code  = $provider->getScope() . $code;
                $alias = 'cfv_' . $code;

                if (isset($item['grid']['query'])) {
                    $query->andWhere($alias . '.value ' . $item['grid']['query']['operator'] . ' :' . $alias);
                    $query->setParameter($alias, $item['grid']['query']['value']);
                }

                $query->leftJoin(
                    'cf.values',
                    $alias,
                    'WITH',
                    $alias . ".code='" . $code . "' AND " . $alias . ".scope='" . $provider->getScope() . "'"
                );
                $query->addSelect($alias . '.value as ' . $code, true);
            }
        }

        return $query;
    }
}
