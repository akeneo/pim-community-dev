<?php

namespace Pim\Bundle\DataGridBundle\Datagrid\Flexible;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Sorter\Configuration as OrmSorterConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;

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
     * @param array
     */
    protected $attributes;

    /**
     * @param \Closure
     */
    protected $callback;

    /**
     * @param DatagridConfiguration $configuration the grid config
     * @param ConfigurationRegistry $registry      the conf registry
     * @param array                 $attributes    the attributes
     * @param Closure               $callback      the callback function
     */
    public function __construct(
        DatagridConfiguration $configuration,
        ConfigurationRegistry $registry,
        $attributes,
        \Closure $callback
    ) {
        $this->configuration = $configuration;
        $this->registry      = $registry;
        $this->attributes    = $attributes;
        $this->callback      = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $columns = $this->configuration->offsetGetByPath(
            sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY)
        );
        foreach ($this->attributes as $attributeCode => $attribute) {
            $attributeType     = $attribute->getAttributeType();
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

                if (!array_key_exists('sorter', $attributeTypeConf) || $attributeTypeConf['sorter'] !== null) {
                    $this->configuration->offsetSetByPath(
                        sprintf('%s[%s]', OrmSorterConfiguration::COLUMNS_PATH, $attributeCode),
                        array(
                            PropertyInterface::DATA_NAME_KEY => $attributeCode,
                            'apply_callback'                 => $this->callback
                        )
                    );
                }
            }
        }
    }
}
