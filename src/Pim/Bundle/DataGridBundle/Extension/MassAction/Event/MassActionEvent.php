<?php

namespace Pim\Bundle\DataGridBundle\Extension\MassAction\Event;

use Symfony\Component\EventDispatcher\Event;
use Pim\Bundle\DataGridBundle\Datasource\DatasourceInterface;

/**
 * Mass action event allows to do add easily some extra code
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassActionEvent extends Event
{
    /**
     * @var DatasourceInterface $datasource
     */
    protected $datasource;

    /**
     * Constructor
     *
     * @param DatasourceInterface $datasource
     */
    public function __construct(DatasourceInterface $datasource)
    {
        $this->datasource = $datasource;
    }

    /**
     * Get datasource
     *
     * @return DatasourceInterface
     */
    public function getDatasource()
    {
        return $this->datasource;
    }
}
