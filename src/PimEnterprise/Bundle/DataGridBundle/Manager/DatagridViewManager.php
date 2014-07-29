<?php

namespace PimEnterprise\Bundle\DataGridBundle\Manager;

use Doctrine\ORM\EntityRepository;
use Pim\Bundle\DataGridBundle\Manager\DatagridViewManager as BaseDatagridViewManager;
use Oro\Bundle\DataGridBundle\Datagrid\Manager as DatagridManager;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Datagrid view manager
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class DatagridViewManager extends BaseDatagridViewManager
{
    /** @var SecurityContextInterface */
    protected $securityContext;

    /**
     * Constructor
     *
     * @param EntityRepository         $repository
     * @param DatagridManager          $datagridManager
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(
        EntityRepository $repository,
        DatagridManager $datagridManager,
        SecurityContextInterface $securityContext
    ) {
        parent::__construct($repository, $datagridManager);
        $this->securityContext = $securityContext;
    }

    /**
     * {@inheritdoc}
     */
    public function findPublic($alias)
    {
        $views = [];
        $allViews = $this->datagridViewRepository->findBy(
            [
                'datagridAlias' => $alias,
                'type'          => DatagridView::TYPE_PUBLIC
            ]
        );

        foreach ($allViews as $view) {
            if ($this->securityContext->isGranted(Attributes::VIEW_DATAGRID_VIEW, $view)) {
                $views[] = $view;
            }
        }

        return $views;
    }
}
