<?php

namespace Pim\Bundle\DataGridBundle\EventListener\MongoDB;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;

/**
 * History grid listener to reconfigure it for MongoDB
 * TODO: find a way to override or merge grids' configurations to remove this listener
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfigureHistoryGridListener
{
    /**
     * Reconfigure columns, filters and sorters
     *
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();
        $config->offsetSetByPath('[columns][author][type]', 'author_property');
    }
}
