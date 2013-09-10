<?php

namespace Oro\Bundle\EntityConfigBundle\Datagrid;

use Doctrine\ORM\Query;

use Oro\Bundle\GridBundle\Action\ActionInterface;

use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Datagrid\ResultRecord;

use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;

use Oro\Bundle\GridBundle\Filter\FilterInterface;

use Oro\Bundle\GridBundle\Property\ActionConfigurationProperty;
use Oro\Bundle\GridBundle\Property\UrlProperty;

use Oro\Bundle\EntityConfigBundle\Config\ConfigModelManager;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;

use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;
use Oro\Bundle\EntityConfigBundle\Provider\PropertyConfigContainer;

class EntityFieldsDatagridManager extends DatagridManager
{
    /**
     * @var FieldDescriptionCollection
     */
    protected $fieldsCollection;

    /**
     * @var integer id
     */
    protected $entityId;

    /**
     * @var ConfigManager
     */
    protected $configManager;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

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
            foreach ($provider->getPropertyConfig()->getGridActions(PropertyConfigContainer::TYPE_FIELD) as $config) {
                $properties[] = new UrlProperty(
                    strtolower($config['name']) . '_link',
                    $this->router,
                    $config['route'],
                    (isset($config['args']) ? $config['args'] : array())
                );

                if (isset($config['filter'])) {
                    $filters[strtolower($config['name'])] = $config['filter'];
                }

                $actions[strtolower($config['name'])] = true;
            }
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

        $this->addDynamicRows($fieldsCollection);
    }

    /**
     * @param      $fieldsCollection
     */
    protected function addDynamicRows($fieldsCollection)
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

        foreach ($this->configManager->getProviders() as $provider) {
            foreach ($provider->getPropertyConfig()->getGridActions(PropertyConfigContainer::TYPE_FIELD) as $config) {
                $configItem = array(
                    'name'         => strtolower($config['name']),
                    'acl_resource' => isset($config['acl_resource']) ? $config['acl_resource'] : 'root',
                    'options'      => array(
                        'label' => ucfirst($config['name']),
                        'icon'  => isset($config['icon']) ? $config['icon'] : 'question-sign',
                        'link'  => strtolower($config['name']) . '_link'
                    )
                );

                if (isset($config['type'])) {
                    switch ($config['type']) {
                        case 'delete':
                            $configItem['type'] = ActionInterface::TYPE_DELETE;
                            break;
                        case 'redirect':
                            $configItem['type'] = ActionInterface::TYPE_REDIRECT;
                            break;
                        case 'ajax':
                            $configItem['type'] = ActionInterface::TYPE_AJAX;
                            break;
                    }
                } else {
                    $configItem['type'] = ActionInterface::TYPE_REDIRECT;
                }

                $actions[] = $configItem;
            }
        }

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
