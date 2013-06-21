<?php

namespace Oro\Bundle\EntityConfigBundle\Datagrid;

use Doctrine\ORM\Query;

use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;

use Oro\Bundle\EntityConfigBundle\ConfigManager;

use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty;

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
     * {@inheritDoc}
     */
    protected function getProperties()
    {
        return array(
            new UrlProperty('view_link', $this->router, 'oro_entityconfig_view', array('id')),
            new UrlProperty('update_link', $this->router, 'oro_entityconfig_update', array('id')),
            //new UrlProperty('remove_link', $this->router, 'oro_entityconfig_remove', array('is')),
            new UrlProperty('fields_link', $this->router, 'oro_entityconfig_fields', array('id')),
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldObjectId = new FieldDescription();
        $fieldObjectId->setName('Id');
        $fieldObjectId->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_INTEGER,
                'label'       => 'Id',
                'field_name'  => 'id',
                'filter_type' => FilterInterface::TYPE_NUMBER,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => false,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldObjectId);

        $fieldObjectName = new FieldDescription();
        $fieldObjectName->setName('className');
        $fieldObjectName->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'Class Name',
                'field_name'  => 'className',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => false,
                'show_filter' => false,
            )
        );
        $fieldsCollection->add($fieldObjectName);

        foreach ($this->configManager->getProviders() as $provider) {
            foreach ($provider->getConfigContainer()->getEntityItems() as $code => $item) {
                if (isset($item['grid'])) {
                    $fieldObjectName = new FieldDescription();
                    $fieldObjectName->setName($code);
                    $fieldObjectName->setOptions(array_merge($item['grid'], array(
                        'expression' => 'cev' . $code . '.value',
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

        $fieldsAction = array(
            'name'         => 'fields',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label' => 'Fields',
                'icon'  => 'th-list',
                'link'  => 'fields_link',
            )
        );

        $deleteAction = array(
            'name'         => 'delete',
            'type'         => ActionInterface::TYPE_DELETE,
            'acl_resource' => 'root',
            'options'      => array(
                'label' => 'Delete',
                'icon'  => 'trash',
                'link'  => 'delete_link',
            )
        );

        return array($viewAction, $updateAction, $fieldsAction);
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
                $alias = 'cev'. $code;
                $query->leftJoin('ce.values', $alias, 'WITH', $alias . ".code='".$code . "' AND " . $alias . ".scope='" . $provider->getScope() . "'");
                $query->addSelect($alias . '.value as '. $code, true);
            }
        }

        return $query;
    }
}
