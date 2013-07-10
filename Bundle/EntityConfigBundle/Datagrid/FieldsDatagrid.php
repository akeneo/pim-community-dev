<?php

namespace Oro\Bundle\EntityConfigBundle\Datagrid;

use Doctrine\ORM\Query;

use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Property\UrlProperty;

use Oro\Bundle\EntityConfigBundle\Entity\ConfigEntity;
use Oro\Bundle\EntityConfigBundle\ConfigManager;

class FieldsDatagrid extends DatagridManager
{
    /**
     * @var FieldDescriptionCollection
     */
    protected $fieldsCollection;

    /**
     * @var ConfigEntity id
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
     * @param  ConfigEntity $entity
     * @return array
     */
    public function getLayoutActions(ConfigEntity $entity)
    {
        $actions = array();
        foreach ($this->configManager->getProviders() as $provider) {
            foreach ($provider->getConfigContainer()->getFieldLayoutActions() as $config) {
                if (isset($config['filter'])
                    && !$provider->getConfig($entity->getClassName())->is($config['filter'])
                ) {
                    continue;
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
            new UrlProperty('view_link', $this->router, 'oro_entityconfig_field_view', array('id')),
            new UrlProperty('update_link', $this->router, 'oro_entityconfig_field_update', array('id')),
        );
        foreach ($this->configManager->getProviders() as $provider) {
            foreach ($provider->getConfigContainer()->getFieldGridActions() as $config) {
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
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
    }

    /**
     * @param $fieldsCollection
     * @param bool $checkEntityGrid
     */
    protected function addDynamicRows($fieldsCollection, $checkEntityGrid = false)
    {
        foreach ($this->configManager->getProviders() as $provider) {
            foreach ($provider->getConfigContainer()->getFieldItems($checkEntityGrid) as $code => $item) {
                if (isset($item['grid'])) {
                    $fieldObjectName = new FieldDescription();
                    $fieldObjectName->setName($code);
                    $fieldObjectName->setOptions(array_merge($item['grid'], array(
                        'expression' => 'cfv_' . $code . '.value',
                        'field_name' => $code,
                    )));
                    $fieldsCollection->add($fieldObjectName);
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getRowActions()
    {
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

        $actions = array($viewAction, $updateAction);
        foreach ($this->configManager->getProviders() as $provider) {
            foreach ($provider->getConfigContainer()->getFieldGridActions() as $config) {
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
        $query->innerJoin('cf.entity', 'ce', 'WITH', 'ce.id=' . $this->entityId);
        $query->addSelect('ce.id as entity_id', true);

        foreach ($this->configManager->getProviders() as $provider) {
            foreach ($provider->getConfigContainer()->getFieldItems() as $code => $item) {
                $alias = 'cfv_' . $code;
                $query->leftJoin('cf.values', $alias, 'WITH', $alias . ".code='" . $code . "' AND " . $alias . ".scope='" . $provider->getScope() . "'");
                $query->addSelect($alias . '.value as ' . $code, true);
            }
        }

        return $query;
    }
}
