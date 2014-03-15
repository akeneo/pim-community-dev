<?php

namespace Pim\Bundle\DataGridBundle\Manager;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ContextConfigurator;
use Pim\Bundle\DataGridBundle\Entity\DatagridView;
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
    /** @var EntityManager */
    protected $entityManager;

    /** @var DatagridManager */
    protected $datagridManager;

    /**
     * Constructor
     *
     * @param EntityManager   $entityManager
     * @param DatagridManager $datagridManager
     */
    public function __construct(EntityManager $entityManager, DatagridManager $datagridManager)
    {
        $this->entityManager   = $entityManager;
        $this->datagridManager = $datagridManager;
    }

    /**
     * Returns all public views and the default user's view
     *
     * @param string $alias
     * @param User   $user
     *
     * @return DatagridView
     */
    public function findAllForUser($alias, User $user)
    {
        $views = $this->entityManager->getRepository('PimDataGridBundle:DatagridView')->findBy(
            [
                'datagridAlias' => $alias,
                'type'          => DatagridView::TYPE_PUBLIC
            ]
        );
        array_unshift($views, $this->getDefaultDatagridView($alias, $user));

        return $views;
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
     * Get or create default datagrid view from datagrid alias and user
     *
     * @param string $alias
     * @param User   $user
     *
     * @return DatagridView
     */
    protected function getDefaultDatagridView($alias, User $user)
    {
        $view = $this->entityManager->getRepository('PimDataGridBundle:DatagridView')->findOneBy(
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
}
