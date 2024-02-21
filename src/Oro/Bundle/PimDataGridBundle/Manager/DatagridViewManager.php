<?php

namespace Oro\Bundle\PimDataGridBundle\Manager;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\DataGridBundle\Datagrid\Manager as DatagridManager;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ContextConfigurator;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;

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
    protected $repository;

    /** @var DatagridManager */
    protected $datagridManager;

    /**
     * Constructor
     *
     * @param EntityRepository $repository
     * @param DatagridManager  $datagridManager
     */
    public function __construct(
        EntityRepository $repository,
        DatagridManager $datagridManager
    ) {
        $this->repository = $repository;
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
        return $this->repository->findBy(
            [
                'datagridAlias' => $alias,
                'type'          => DatagridView::TYPE_PUBLIC
            ]
        );
    }

    /**
     * Get datagrid column choices for the provided datagrid alias
     *
     * @param string $alias
     * @param bool   $displayedColumns
     *
     * @return array
     */
    public function getColumnChoices($alias, $displayedColumns = false)
    {
        $choices = [];

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
            $properties = ['label', 'sortOrder', 'group', 'groupOrder'];

            foreach ($columnsConfig as $code => $meta) {
                $choice = ['code' => $code];
                foreach ($properties as $property) {
                    if (isset($meta[$property])) {
                        $choice[$property] = $meta[$property];
                    }
                }
                $choices[$code] = $choice;
            }
        }

        return $choices;
    }

    /**
     * Get default datagrid columns for the provided datagrid alias
     *
     * @param string $alias
     *
     * @return array
     */
    public function getDefaultColumns(string $alias): array
    {
        $path = sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY);

        $columnsConfig = $this
            ->datagridManager
            ->getConfigurationForGrid($alias)
            ->offsetGetByPath($path);

        return is_array($columnsConfig) ? array_keys($columnsConfig) : [];
    }
}
