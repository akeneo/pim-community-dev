<?php

namespace Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;
use Oro\Bundle\DataGridBundle\Extension\Sorter\Configuration as OrmSorterConfiguration;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface;

/**
 * Sorters configurator for product grid
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SortersConfigurator implements ConfiguratorInterface
{
    /** @var DatagridConfiguration */
    protected $configuration;

    /** @var ConfigurationRegistry */
    protected $registry;

    /**
     * @param ConfigurationRegistry $registry the conf registry
     */
    public function __construct(ConfigurationRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(DatagridConfiguration $configuration)
    {
        $this->configuration = $configuration;
        $this->addAttributeSorters();
        $this->removeExtraSorters();
    }

    /**
     * Add sorters for attributes used as columns
     */
    protected function addAttributeSorters()
    {
        $path = sprintf(self::SOURCE_PATH, self::USEABLE_ATTRIBUTES_KEY);
        $attributes = $this->configuration->offsetGetByPath($path);
        $attributes = ($attributes === null) ? [] : $attributes;
        $columns = $this->configuration->offsetGetByPath(
            sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY)
        );
        foreach ($attributes as $attributeCode => $attribute) {
            if (AttributeTypes::IDENTIFIER === $attribute['type']) {
                $attributeCode = 'identifier';
            }

            $attributeType = $attribute['type'];
            $attributeTypeConf = $this->registry->getConfiguration($attributeType);
            $columnExists = isset($columns[$attributeCode]);

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
                        [
                            PropertyInterface::DATA_NAME_KEY => $attributeCode,
                            'sorter'                         => $attributeTypeConf['sorter']
                        ]
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
