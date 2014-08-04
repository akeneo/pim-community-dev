<?php

namespace Pim\Bundle\DataGridBundle\Manager;

use Doctrine\ORM\EntityRepository;
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
    /** @var EntityRepository */
    protected $datagridViewRepository;

    /** @var DatagridManager */
    protected $datagridManager;

    /**
     * Constructor
     *
     * @param EntityRepository $repository
     * @param DatagridManager  $datagridManager
     */
    public function __construct(EntityRepository $repository, DatagridManager $datagridManager)
    {
        $this->datagridViewRepository = $repository;
        $this->datagridManager = $datagridManager;
    }

    /**
     * Returns all public views
     *
     * @param string $alias
     *
     * @return DatagridView
     */
    public function findPublic($alias)
    {
        return $this->datagridViewRepository->findBy(
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
