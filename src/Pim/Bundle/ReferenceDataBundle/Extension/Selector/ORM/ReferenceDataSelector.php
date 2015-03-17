<?php

namespace Pim\Bundle\ReferenceDataBundle\Extension\Selector\ORM;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Pim\Bundle\DataGridBundle\Extension\Selector\SelectorInterface;
use Pim\Component\ReferenceData\ConfigurationRegistry;

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

    /** @var ConfigurationRegistry */
    protected $registry;

    /**
     * @param SelectorInterface     $predecessor
     * @param ConfigurationRegistry $registry
     */
    public function __construct(SelectorInterface $predecessor, ConfigurationRegistry $registry)
    {
        $this->predecessor = $predecessor;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(DatasourceInterface $datasource, DatagridConfiguration $configuration)
    {
        $this->predecessor->apply($datasource, $configuration);
        $referencesData = $this->buildReferenceData($datasource, $configuration);
    }

    /**
     * Build references data
     *
     * @param DatasourceInterface   $datasource
     * @param DatagridConfiguration $configuration
     */
    protected function buildReferenceData(DatasourceInterface $datasource, DatagridConfiguration $configuration)
    {
        $references = [];
        $source = $configuration->offsetGet('source');
        foreach ($source['attributes_configuration'] as $attribute) {
            if (null !== $attribute['referenceDataName'] && !in_array($attribute['referenceDataName'], $references)) {
                $datasource->getQueryBuilder()
                    ->leftJoin('values.' . $attribute['referenceDataName'], $attribute['referenceDataName'])
                    ->addSelect($attribute['referenceDataName']);

                $references[] = $attribute['referenceDataName'];
            }
        }
    }
}
