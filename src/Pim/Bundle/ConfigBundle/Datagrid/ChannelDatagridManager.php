<?php
namespace Pim\Bundle\ConfigBundle\Datagrid;

use Oro\Bundle\GridBundle\Property\FieldProperty;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty;

/**
 * Channel datagrid manager
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ChannelDatagridManager extends DatagridManager
{
    /**
     * get properties
     * @return array
     */
    protected function getProperties()
    {
        $fieldId = new FieldDescription();
        $fieldId->setName('id');
        $fieldId->setOptions(
            array(
                'type'     => FieldDescriptionInterface::TYPE_INTEGER,
                'required' => true,
            )
        );

        return array(
            new FieldProperty($fieldId),
            new UrlProperty('edit_link', $this->router, 'pim_config_channel_edit', array('id')),
            new UrlProperty('delete_link', $this->router, 'pim_config_channel_remove', array('id'))
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $field = new FieldDescription();
        $field->setName('code');
        $field->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => $this->translate('Code'),
                'field_name'  => 'code',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($field);

        $field = new FieldDescription();
        $field->setName('name');
        $field->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => $this->translate('Name'),
                'field_name'  => 'name',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($field);
    }

    /**
     * {@inheritdoc}
     */
    protected function getRowActions()
    {
        $clickAction = array(
            'name'         => 'rowClick',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label'         => $this->translate('Edit'),
                'icon'          => 'edit',
                'link'          => 'edit_link',
                'backUrl'       => true,
                'runOnRowClick' => true
            )
        );

        $editAction = array(
            'name'         => 'edit',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label'   => $this->translate('Edit'),
                'icon'    => 'edit',
                'link'    => 'edit_link',
                'backUrl' => true
            )
        );

        $deleteAction = array(
            'name'         => 'delete',
            'type'         => ActionInterface::TYPE_DELETE,
            'acl_resource' => 'root',
            'options'      => array(
                'label' => $this->translate('Delete'),
                'icon'  => 'trash',
                'link'  => 'delete_link'
            )
        );

        return array($clickAction, $editAction, $deleteAction);
    }
}
