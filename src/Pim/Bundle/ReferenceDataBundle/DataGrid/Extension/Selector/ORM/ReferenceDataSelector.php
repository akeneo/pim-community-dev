<?php

namespace Pim\Bundle\ReferenceDataBundle\DataGrid\Extension\Selector\ORM;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Pim\Bundle\DataGridBundle\Extension\Selector\SelectorInterface;

/**
 * Reference data selector
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataSelector implements SelectorInterface
{
    /** @var SelectorInterface */
    protected $predecessor;

    /**
     * @param SelectorInterface $predecessor
     */
    public function __construct(SelectorInterface $predecessor)
    {
        $this->predecessor = $predecessor;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(DatasourceInterface $dataSource, DatagridConfiguration $configuration)
    {
        $this->predecessor->apply($dataSource, $configuration);
        $this->buildReferenceData($dataSource, $configuration);
    }

    /**
     * Build references data
     *
     * @param DatasourceInterface   $dataSource
     * @param DatagridConfiguration $configuration
     */
    protected function buildReferenceData(DatasourceInterface $dataSource, DatagridConfiguration $configuration)
    {
        $source = $configuration->offsetGet('source');
        $qb = $dataSource->getQueryBuilder();

        foreach ($configuration->offsetGet('columns') as $column => $config) {
            $this->buildQueryBuilder($qb, $source, $column);
        }
    }

    /**
     * Build query builder for all references data displayed in grid
     *
     * @param QueryBuilder $qb
     * @param array        $source
     * @param string       $column
     */
    protected function buildQueryBuilder(QueryBuilder $qb, array $source, $column)
    {
        if (!isset($source['attributes_configuration'][$column])) {
            return;
        }

        $attribute = $source['attributes_configuration'][$column];

        if (isset($attribute['properties']['reference_data_name']) &&
            '' !== $attribute['properties']['reference_data_name']
        ) {
            $referenceDataName = $attribute['properties']['reference_data_name'];

            if (null !== $referenceDataName) {
                $qbJoins = $this->getQbJoins($qb);

                if (!in_array($referenceDataName, $qbJoins)) {
                    $qb->leftJoin('values.' . $referenceDataName, $referenceDataName)
                        ->addSelect($referenceDataName);
                }
            }
        }
    }

    /**
     * Get query builder joins
     *
     * @param QueryBuilder $qb
     *
     * @return array
     */
    protected function getQbJoins(QueryBuilder $qb)
    {
        $qbJoin = [];
        $joins  = $qb->getDQLPart('join');
        $rootAlias = current($qb->getRootAliases());
        if (isset($joins[$rootAlias])) {
            foreach ($joins[$rootAlias] as $join) {
                $qbJoin[] = $join->getAlias();
            }
        }

        return $qbJoin;
    }
}
