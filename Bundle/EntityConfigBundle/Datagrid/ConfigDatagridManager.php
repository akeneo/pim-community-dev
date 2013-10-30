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
     * Todo: update acl resources after impl.
     */
    protected function getRowActions()
    {
        $clickAction = array(
            'name'         => 'rowClick',
            'type'         => ActionInterface::TYPE_REDIRECT,
            //'acl_resource' => '(root)',
            'options'      => array(
                'label'         => 'View',
                'link'          => 'view_link',
                'runOnRowClick' => true,
            )
        );

        $viewAction = array(
            'name'         => 'view',
            'type'         => ActionInterface::TYPE_REDIRECT,
            //'acl_resource' => 'root',
            'options'      => array(
                'label' => 'View',
                'icon'  => 'book',
                'link'  => 'view_link',
            )
        );

        $updateAction = array(
            'name'         => 'update',
            'type'         => ActionInterface::TYPE_REDIRECT,
            //'acl_resource' => 'root',
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
}
