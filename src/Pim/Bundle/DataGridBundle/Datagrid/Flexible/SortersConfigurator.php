<?php

namespace Pim\Bundle\DataGridBundle\Datagrid\Flexible;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Sorter\Configuration as OrmSorterConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;
use Pim\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

/**
 * Sorters configurator for flexible grid
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SortersConfigurator implements ConfiguratorInterface
{
    /**
     * @param DatagridConfiguration
     */
    protected $configuration;

    /**
     * @param ConfigurationRegistry
     */
    protected $registry;

    /**
     * @var FlexibleManager
     */
    protected $flexibleManager;

    /**
     * @param DatagridConfiguration $configuration the grid config
     * @param ConfigurationRegistry $registry      the conf registry
     * @param FlexibleManager       $manager       flexible manager
     */
    public function __construct(
        DatagridConfiguration $configuration,
        ConfigurationRegistry $registry,
        FlexibleManager $manager
    ) {
        $this->configuration   = $configuration;
        $this->registry        = $registry;
        $this->flexibleManager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->addAttributeSorters();
        $this->removeExtraSorters();
    }

    /**
     * Add sorters for attributes used as columns
     */
    protected function addAttributeSorters()
    {
        $attributes = $this->configuration->offsetGetByPath(OrmDatasource::USEABLE_ATTRIBUTES_PATH);
        $columns = $this->configuration->offsetGetByPath(
            sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY)
        );
        foreach ($attributes as $attributeCode => $attribute) {
            $attributeType     = $attribute['attributeType'];
            $attributeTypeConf = $this->registry->getConfiguration($attributeType);
            $columnExists      = isset($columns[$attributeCode]);

            if (!$attributeTypeConf || !isset($attributeTypeConf['column'])) {
                throw new \LogicException(
                    sprintf(
                        'Attribute type %s must be configured to display sorter for attribute %s',
                        $attributeType,
                        $attributeCode
                    )
                );
            }

            if ($columnExists && $attributeTypeConf && $attributeTypeConf['column']) {
                if (isset($attributeTypeConf['sorter'])) {
                    $this->configuration->offsetSetByPath(
                        sprintf('%s[%s]', OrmSorterConfiguration::COLUMNS_PATH, $attributeCode),
                        array(
                            PropertyInterface::DATA_NAME_KEY => $attributeCode,
                            'sorter'                         => $attributeTypeConf['sorter']
                        )
                    );
                }
            }
        }
    }

    /**
     * Remove extra sorters, ie, sorters defined in datagrid.yml but columns are not displayed
     */
    protected function removeExtraSorters()
    {
        $displayedColumns = $this->configuration->offsetGetByPath(sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY));
        $columnsCodes = array_keys($displayedColumns);
        $sorters = $this->configuration->offsetGetByPath(sprintf('%s', OrmSorterConfiguration::COLUMNS_PATH));

        if (!empty($sorters)) {
            $sortersCodes = array_keys($sorters);

            foreach ($sortersCodes as $sorterCode) {
                if (!in_array($sorterCode, $columnsCodes)) {
                    unset($sorters[$sorterCode]);
                }
            }

            $this->configuration->offsetSetByPath(
                sprintf('%s', OrmSorterConfiguration::COLUMNS_PATH),
                $sorters
            );
        }
    }
}
