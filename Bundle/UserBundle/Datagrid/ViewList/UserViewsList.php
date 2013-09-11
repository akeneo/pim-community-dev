<?php

namespace Oro\Bundle\UserBundle\Datagrid\ViewList;

use Oro\Bundle\FilterBundle\Form\Type\Filter\DateRangeFilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\TextFilterType;
use Oro\Bundle\GridBundle\Datagrid\Views\AbstractViewsList;
use Oro\Bundle\GridBundle\Datagrid\Views\View;

class UserViewsList extends AbstractViewsList
{
    /**
     * Returns an array of available views
     *
     * @return View[]
     */
    protected function getViewsList()
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $lastDay = $now->sub(new \DateInterval('P1D'))->format('m/d/Y H:i:s');

        return array(
            new View(
                'test.user.view.active',
                array(
                    'enabled' => array(
                        'value' => 1,
                    )
                )
            ),
            new View(
                'test.user.view.last_active',
                array(
                    'enabled' => array(
                        'value' => 1,
                    ),
                    'created' => array(
                        'value' => array(
                            'start' => $lastDay,
                        ),
                        'type' => DateRangeFilterType::TYPE_MORE_THAN
                    )
                )
            ),
            new View(
                'test.user.view.sort_by_name_desc',
                array(),
                array(
                    'firstName' => 'DESC',
                )
            ),
        );
    }
}
