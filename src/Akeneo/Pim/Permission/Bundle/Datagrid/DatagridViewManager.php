<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Datagrid;

use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\DataGridBundle\Datagrid\Manager as DatagridManager;
use Oro\Bundle\PimDataGridBundle\Manager\DatagridViewManager as BaseDatagridViewManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Datagrid view manager
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class DatagridViewManager extends BaseDatagridViewManager
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;
    /** @var SaverInterface */
    private $saver;
    /** @var RemoverInterface */
    private $remover;

    /**
     * Constructor
     *
     * @param EntityRepository              $repository
     * @param DatagridManager               $datagridManager
     * @param SaverInterface                $saver
     * @param RemoverInterface              $remover
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        EntityRepository $repository,
        DatagridManager $datagridManager,
        SaverInterface $saver,
        RemoverInterface $remover,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        parent::__construct($repository, $datagridManager);

        $this->saver = $saver;
        $this->remover = $remover;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function findPublic($alias)
    {
        $views = parent::findPublic($alias);

        foreach ($views as $key => $view) {
            if (false === $this->authorizationChecker->isGranted(Attributes::VIEW, $view)) {
                unset($views[$key]);
            }
        }

        return $views;
    }
}
