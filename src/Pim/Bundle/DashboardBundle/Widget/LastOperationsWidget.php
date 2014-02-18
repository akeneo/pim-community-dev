<?php

namespace Pim\Bundle\DashboardBundle\Widget;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\DashboardBundle\Entity\Repository\WidgetRepository;

/**
 * Widget to display last import/export operations
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LastOperationsWidget implements WidgetInterface
{
    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var WidgetRepository */
    protected $repository;

    /**
     * @param SecurityFacade   $securityFacade
     * @param WidgetRepository $repository
     */
    public function __construct(SecurityFacade $securityFacade, WidgetRepository $repository)
    {
        $this->securityFacade = $securityFacade;
        $this->repository     = $repository;
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
        $types = array_filter(
            ['import', 'export'],
            function ($type) {
                return $this->securityFacade->isGranted(sprintf('pim_importexport_%s_execution_show', $type));
            }
        );

        $params = empty($types) ? [] : $this->repository->getLastOperationsData($types);

        return ['params' => $params];
    }
}
