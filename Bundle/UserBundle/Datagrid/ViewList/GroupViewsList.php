<?php

namespace Oro\Bundle\UserBundle\Datagrid\ViewList;

use Oro\Bundle\FilterBundle\Form\Type\Filter\TextFilterType;
use Oro\Bundle\GridBundle\Datagrid\Views\View;
use Oro\Bundle\GridBundle\Datagrid\Views\AbstractViewsList;

class GroupViewsList extends AbstractViewsList
{
    /**
     * Returns an array of available views
     *
     * @return View[]
     */
    protected function getViewsList()
    {
        return array(
            new View(
                'test.group.name',
                array(
                    'name' => array(
                        'value' => 'admin',
                        'type' => TextFilterType::TYPE_CONTAINS,
                    )
                )
            )
        );
    }
}
