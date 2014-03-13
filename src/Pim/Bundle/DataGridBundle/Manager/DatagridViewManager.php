<?php

namespace Pim\Bundle\DataGridBundle\Manager;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ContextConfigurator;
use Pim\Bundle\DataGridBundle\Entity\DatagridView;
use Pim\Bundle\DataGridBundle\Entity\Repository\DatagridViewRepository;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Manager as DatagridManager;

/**
 * Datagrid view manager
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridViewManager
{
    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var EntityManager */
    protected $entityManager;

    /** @var DatagridManager */
    protected $datagridManager;

    /** @var DatagridViewRepository */
    protected $repository;

    /**
     * Constructor
     *
     * @param SecurityContextInterface $securityContext
     * @param EntityManager            $entityManager
     * @param DatagridManager          $datagridManager
     * @param DatagridViewRepository   $repository
     */
    public function __construct(
        SecurityContextInterface $securityContext,
        EntityManager $entityManager,
        DatagridManager $datagridManager,
        DatagridViewRepository $repository
    ) {
        $this->securityContext = $securityContext;
        $this->entityManager   = $entityManager;
        $this->datagridManager = $datagridManager;
        $this->repository      = $repository;
    }

    /**
     * Get or create default datagrid view from datagrid alias and user
     *
     * @param string $alias
     * @param User   $user
     *
     * @return DatagridView
     */
    public function getDefaultDatagridView($alias, User $user)
    {
        $view = $this->repository->findOneBy(
            [
                'datagridAlias' => $alias,
                'owner'         => $user,
                'type'          => DatagridView::TYPE_DEFAULT
            ]
        );

        if (!$view) {
            $view = new DatagridView();
            $view
                ->setType(DatagridView::TYPE_DEFAULT)
                ->setOwner($user)
                ->setDatagridAlias($alias)
                ->setColumns(array_keys($this->getColumnChoices($alias)));

            $this->entityManager->persist($view);
            $this->entityManager->flush();
        }

        return $view;
    }

    /**
     * Check if the view can be edited by the current user, if not - prepare and return the user's custom view
     *
     * @param DatagridView $view
     *
     * @return DatagridView
     */
    public function getEditableDatagridView(DatagridView $view)
    {
        $user = $this->getUser();
        if ($view->getOwner() === $user) {
            return $view;
        }

        $customView = $this->repository->findOneBy(
            [
                'datagridAlias' => $view->getDatagridAlias(),
                'owner'         => $user,
                'type'          => DatagridView::TYPE_CUSTOM
            ]
        );

        if (!$customView) {
            $customView = clone $view;
        }

        $customView
            ->setOwner($user)
            ->setLabel(null)
            ->setType(DatagridView::TYPE_CUSTOM)
            ->setColumns($view->getColumns())
            ->setFilters($view->getFilters());

        $this->entityManager->persist($customView);
        $this->entityManager->flush();

        return $customView;
    }

    /**
     * Get datagrid column choices for the provided datagrid alias
     *
     * @param string $alias
     *
     * @return array
     */
    public function getColumnChoices($alias)
    {
        $choices = array();

        $columnsConfig = $this
            ->datagridManager
            ->getDatagrid($alias)
            ->getAcceptor()
            ->getConfig()
            ->offsetGetByPath(sprintf(ContextConfigurator::SOURCE_PATH, ContextConfigurator::AVAILABLE_COLUMNS_KEY));

        if ($columnsConfig) {
            foreach ($columnsConfig as $code => $meta) {
                $choices[$code] = $meta['label'];
            }
        }

        return $choices;
    }

    /**
     * Get the current user from the Security Context
     *
     * @return User|null
     */
    protected function getUser()
    {
        if (null === $token = $this->securityContext->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }
}
