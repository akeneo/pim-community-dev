<?php

namespace Oro\Bundle\EntityConfigBundle\Datagrid;

use Doctrine\ORM\Query;

use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty;

use Oro\Bundle\EntityConfigBundle\ConfigManager;

class ConfigDatagridManager extends DatagridManager
{
    /**
     * @var FieldDescriptionCollection
     */
    protected $fieldsCollection;

    /**
     * @var ConfigManager
     */
    protected $configManager;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * @return array
     */
    public function getLayoutActions()
    {
        $actions = array();

        foreach ($this->configManager->getProviders() as $provider) {
            foreach ($provider->getConfigContainer()->getEntityLayoutActions() as $config) {
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
            new UrlProperty('view_link', $this->router, 'oro_entityconfig_view', array('id')),
            new UrlProperty('update_link', $this->router, 'oro_entityconfig_update', array('id')),
        );

        foreach ($this->configManager->getProviders() as $provider) {
            foreach ($provider->getConfigContainer()->getEntityGridActions() as $config) {
                $properties[] = new UrlProperty(
                    strtolower($config['name']) . '_link',
                    $this->router,
                    $config['route'],
                    (isset($config['args']) ? $config['args'] : array())
                );
            }
        }

        return $properties;
    }

    /**
     * @param  string $scope
     * @return array
     */
    protected function getObjectName($scope = 'name')
    {
        $options = array('name'=> array(), 'module'=> array());

        $query = $this->createQuery()->getQueryBuilder()
            ->add('select', 'a.className')
            ->add('from', 'Oro\Bundle\EntityConfigBundle\Entity\ConfigEntity a')
            ->distinct('a.className');

        $result = $query->getQuery()->getArrayResult();

        foreach ((array) $result as $value) {
            $className = explode('\\', $value['className']);

            $options['name'][$value['className']] = '';
            $options['module'][$value['className']] = '';

            foreach ($className as $index => $name) {
                if (count($className)-1 == $index) {
                    $options['name'][$value['className']] = $name;
                } elseif (!in_array($name, array('Bundle','Entity'))) {
                    $options['module'][$value['className']] .= $name;
                }
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
            foreach ($provider->getConfigContainer()->getEntityItems() as $code => $item) {
                if (isset($item['grid'])) {
                    $fieldObjectProvider = new FieldDescription();
                    $fieldObjectProvider->setName($code);
                    $fieldObjectProvider->setOptions(
                        array_merge(
                            $item['grid'],
                            array(
                                'expression' => 'cev' . $code . '.value',
                                'field_name' => $code,
                            )
                        )
                    );

                    if (isset($item['priority']) && !isset($fields[$item['priority']])) {
                        $fields[$item['priority']] = $fieldObjectProvider;
                    } else {
                        $fields[] = $fieldObjectProvider;
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

        $fieldObjectId = new FieldDescription();
        $fieldObjectId->setName('id');
        $fieldObjectId->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_INTEGER,
                'label'       => 'Id',
                'field_name'  => 'id',
                'filter_type' => FilterInterface::TYPE_NUMBER,
                'required'    => false,
                'sortable'    => false,
                'filterable'  => false,
                'show_filter' => false,
                'show_column' => false,
            )
        );
        $fieldsCollection->add($fieldObjectId);

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

        foreach ($this->configManager->getProviders() as $provider) {
            foreach ($provider->getConfigContainer()->getEntityGridActions() as $config) {
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

        foreach ($this->configManager->getProviders() as $provider) {
            foreach ($provider->getConfigContainer()->getEntityItems() as $code => $item) {
                $alias = 'cev' . $code;
                $query->leftJoin('ce.values', $alias, 'WITH', $alias . ".code='" . $code . "' AND " . $alias . ".scope='" . $provider->getScope() . "'");
                $query->addSelect($alias . '.value as ' . $code, true);
            }
        }

        return $query;
    }
}
