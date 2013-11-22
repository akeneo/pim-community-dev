<?php

namespace Pim\Bundle\CustomEntityBundle\Datagrid;

use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Property\FieldProperty;
use Oro\Bundle\GridBundle\Datagrid\DatagridManager as OroDatagridManager;

/**
 * Datagrid manager for custom entities
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridManager extends OroDatagridManager
{
    /**
     * @var string
     */
    protected $customEntityName;

    /**
     * {@inheritdoc}
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
            new UrlProperty(
                'edit_link',
                $this->router,
                'pim_customentity_edit',
                $this->customEntityName,
                array('id')
            ),
            new UrlProperty(
                'delete_link',
                $this->router,
                'pim_customentity_remove',
                $this->customEntityName,
                array('id')
            )
        );
    }

    /**
     * Sets the custom entity name
     *
     * @param string $customEntityName
     */
    public function setCustomEntityName($customEntityName)
    {
        $this->customEntityName = $customEntityName;
    }
}
