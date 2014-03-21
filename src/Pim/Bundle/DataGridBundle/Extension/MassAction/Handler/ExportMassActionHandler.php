<?php

namespace Pim\Bundle\DataGridBundle\Extension\MassAction\Handler;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;

/**
 * Export action handler
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExportMassActionHandler implements MassActionHandlerInterface
{
    /**
     * @var HydratorInterface $hydrator
     */
    protected $hydrator;

    /**
     * Constructor
     *
     * @param HydratorInterface $hydrator
     */
    public function __construct(HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(DatagridInterface $datagrid, MassActionInterface $massAction)
    {
        $datasource = $datagrid->getDatasource();
        $datasource->setHydrator($this->hydrator);

        return $datasource->getResults();
    }
}
