<?php

namespace Pim\Bundle\DashboardBundle\Widget;

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
    /** @var WidgetRepository */
    protected $repository;

    /**
     * @param WidgetRepository $repository
     */
    public function __construct(WidgetRepository $repository)
    {
        $this->repository = $repository;
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
            'params' => $this->repository->getLastOperationsData()
        ];
    }
}
