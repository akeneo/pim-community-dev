<?php

namespace Oro\Bundle\EmailBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Extension\GridViews\View;
use Oro\Bundle\DataGridBundle\Extension\GridViews\AbstractViewsList;

class EmailTemplatesViewList extends AbstractViewsList
{
    /**
     * {@inheritDoc}
     */
    protected function getViewsList()
    {
        return array(
            new View(
                'oro.email.datagrid.emailtemplate.view.system_templates',
                array(
                    'isSystem' => array(
                        'value' => 1,
                    ),
                )
            )
        );
    }
}
