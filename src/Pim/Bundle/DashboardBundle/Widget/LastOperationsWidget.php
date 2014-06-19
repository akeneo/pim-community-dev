<?php

namespace Pim\Bundle\DashboardBundle\Widget;

use Pim\Bundle\ImportExportBundle\Manager\JobExecutionManager;

/**
 * Widget to display last import/export operations
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LastOperationsWidget implements WidgetInterface
{
    /** @var JobExecutionManager */
    protected $manager;

    /**
     * @param JobExecutionManager $manager
     */
    public function __construct(JobExecutionManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return 'PimDashboardBundle:Widget:last_operations.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return [
            'params' => $this->manager->getLastOperationsData(['import', 'export'])
        ];
    }
}
