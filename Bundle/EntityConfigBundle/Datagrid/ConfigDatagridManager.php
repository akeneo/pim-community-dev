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
use Oro\Bundle\GridBundle\Property\TwigTemplateProperty;

use Oro\Bundle\EntityConfigBundle\Config\ConfigModelManager;
use Oro\Bundle\EntityConfigBundle\Provider\PropertyConfigContainer;

class ConfigDatagridManager extends BaseDatagrid
{
    /**
     * @return array
     */
    public function getLayoutActions()
    {
        $actions = array();

        foreach ($this->configManager->getProviders() as $provider) {
            foreach ($provider->getPropertyConfig()->getLayoutActions() as $config) {
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
                $provider->getPropertyConfig()->getRequireJsModules()
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
            new UrlProperty('view_link', $this->router, 'oro_entityconfig_view', array('id')),
            new UrlProperty('update_link', $this->router, 'oro_entityconfig_update', array('id')),
        );

        $filters = array();
        $actions = array();

        foreach ($this->configManager->getProviders() as $provider) {
            $gridActions = $provider->getPropertyConfig()->getGridActions();

            $this->prepareProperties($gridActions, $properties, $actions, $filters);

            if ($provider->getPropertyConfig()->getUpdateActionFilter()) {
                $filters['update'] = $provider->getPropertyConfig()->getUpdateActionFilter();
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

                        $actions['update'] = false;
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
     * @param  string $scope
     * @return array
     */
    protected function getObjectName($scope = 'name')
    {
        $options = array('name' => array(), 'module' => array());

        $query = $this->createQuery()->getQueryBuilder()
            ->add('select', 'ce.className')
            ->distinct('ce.className');

        $result = $query->getQuery()->getArrayResult();

        foreach ((array) $result as $value) {
            $className = explode('\\', $value['className']);

            $options['name'][$value['className']]   = '';
            $options['module'][$value['className']] = '';

            if (strpos($value['className'], 'Extend\\Entity') === false) {
                foreach ($className as $index => $name) {
                    if (count($className) - 1 == $index) {
                        $options['name'][$value['className']] = $name;
                    } elseif (!in_array($name, array('Bundle', 'Entity'))) {
                        $options['module'][$value['className']] .= $name;
                    }
                }
            } else {
                $options['name'][$value['className']]   = str_replace('Extend\\Entity\\', '', $value['className']);
                $options['module'][$value['className']] = 'System';
            }
        }

        return $options[$scope];
    }

    /**
     * @param FieldDescriptionCollection $fieldsCollection
     */
    protected function getDynamicFields(FieldDescriptionCollection $fieldsCollection)
    {
        $fields = array();
        foreach ($this->configManager->getProviders() as $provider) {
            foreach ($provider->getPropertyConfig()->getItems() as $code => $item) {
                if (isset($item['grid'])) {
                    $item['grid'] = $provider->getPropertyConfig()->initConfig($item['grid']);

                    $fieldObject = new FieldDescription();
                    $fieldObject->setName($code);
                    $fieldObject->setOptions(
                        array_merge(
                            $item['grid'],
                            array(
                                'expression' => 'cev' . $code . '.value',
                                'field_name' => $code,
                            )
                        )
                    );

                    if (isset($item['grid']['type'])
                        && $item['grid']['type'] == FieldDescriptionInterface::TYPE_HTML
                        && isset($item['grid']['template'])
                    ) {
                        $templateDataProperty = new TwigTemplateProperty(
                            $fieldObject,
                            $item['grid']['template']
                        );
                        $fieldObject->setProperty($templateDataProperty);
                    }

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
        $this->getDynamicFields($fieldsCollection);

        $fieldObjectName = new FieldDescription();
        $fieldObjectName->setName('name');
        $fieldObjectName->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_OPTIONS,
                'label'       => 'Name',
                'field_name'  => 'className',
                'filter_type' => FilterInterface::TYPE_CHOICE,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
                'choices'     => $this->getObjectName(),
                'multiple'    => true,
            )
        );
        $fieldsCollection->add($fieldObjectName);

        $fieldObjectModule = new FieldDescription();
        $fieldObjectModule->setName('module');
        $fieldObjectModule->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_OPTIONS,
                'label'       => 'Module',
                'field_name'  => 'className',
                'filter_type' => FilterInterface::TYPE_CHOICE,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
                'choices'     => $this->getObjectName('module'),
                'multiple'    => true,
            )
        );
        $fieldsCollection->add($fieldObjectModule);

        $fieldObjectCreate = new FieldDescription();
        $fieldObjectCreate->setName('created');
        $fieldObjectCreate->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_DATETIME,
                'label'       => 'Create At',
                'field_name'  => 'created',
                'filter_type' => FilterInterface::TYPE_DATETIME,
                'required'    => true,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldObjectCreate);

        $fieldObjectUpdate = new FieldDescription();
        $fieldObjectUpdate->setName('updated');
        $fieldObjectUpdate->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_DATETIME,
                'label'       => 'Update At',
                'field_name'  => 'updated',
                'filter_type' => FilterInterface::TYPE_DATETIME,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldObjectUpdate);
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
                'label'         => 'View',
                'link'          => 'view_link',
                'runOnRowClick' => true,
            )
        );

        $viewAction = array(
            'name'         => 'view',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label' => 'View',
                'icon'  => 'book',
                'link'  => 'view_link',
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

        $actions = array($clickAction, $viewAction, $updateAction);

        $this->prepareRowActions($actions);

        return $actions;
    }

    /**
     * @param ProxyQueryInterface $query
     * @return ProxyQueryInterface
     */
    protected function prepareQuery(ProxyQueryInterface $query)
    {
        foreach ($this->configManager->getProviders() as $provider) {
            foreach ($provider->getPropertyConfig()->getItems() as $code => $item) {
                $alias = 'cev' . $code;

                if (isset($item['grid']['query'])) {
                    $query->andWhere($alias . '.value ' . $item['grid']['query']['operator'] . ' :' . $alias);
                    $query->setParameter($alias, $item['grid']['query']['value']);
                }

                $query->leftJoin(
                    'ce.values',
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
