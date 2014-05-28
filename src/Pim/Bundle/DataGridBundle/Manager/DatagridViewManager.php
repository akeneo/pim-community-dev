<?php

namespace Pim\Bundle\DataGridBundle\Manager;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ContextConfigurator;
use Pim\Bundle\DataGridBundle\Entity\DatagridView;
use Oro\Bundle\DataGridBundle\Datagrid\Manager as DatagridManager;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;

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
        return $this->entityManager->getRepository('PimDataGridBundle:DatagridView')->findBy(
            [
                'datagridAlias' => $alias,
                'type'          => DatagridView::TYPE_PUBLIC
            ]
        );
    }

    /**
     * Get datagrid column choices for the provided datagrid alias
     *
     * @param string  $alias
     * @param boolean $displayedColumns
     *
     * @return array
     */
    public function getColumnChoices($alias, $displayedColumns = false)
    {
        $choices = array();

        $path = (true === $displayedColumns) ?
            sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY) :
            sprintf(ContextConfigurator::SOURCE_PATH, ContextConfigurator::AVAILABLE_COLUMNS_KEY);

        $columnsConfig = $this
            ->datagridManager
            ->getDatagrid($alias)
            ->getAcceptor()
            ->getConfig()
            ->offsetGetByPath($path);

        if ($columnsConfig) {
            foreach ($columnsConfig as $code => $meta) {
                $choices[$code] = $meta['label'];
            }
        }

        return $choices;
    }
}
