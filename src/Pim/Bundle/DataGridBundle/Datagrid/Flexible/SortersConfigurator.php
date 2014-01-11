<?php

namespace Pim\Bundle\DataGridBundle\Datagrid\Flexible;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Sorter\Configuration as OrmSorterConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;
use Pim\Bundle\DataGridBundle\Extension\Formatter\Property\FlexibleFieldProperty;

/**
 * Sorters configurator for flexible grid
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SortersConfigurator
{
    /**
     * @param DatagridConfiguration
     */
    protected $configuration;

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
     * @param array                 $attributes    the attributes
     * @param Closure               $callback      the callback function
     */
    public function __construct(DatagridConfiguration $configuration, $attributes, \Closure $callback)
    {
        $this->configuration = $configuration;
        $this->attributes    = $attributes;
        $this->callback      = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        foreach ($this->attributes as $attributeCode => $attribute) {
            $showColumn    = $attribute->isUseableAsGridColumn();
            $attributeType = $attribute->getAttributeType();

            // TODO : to fix
            if (in_array($attributeType, array('pim_catalog_file', 'pim_catalog_image'))) {
                continue;
            }

            if ($showColumn) {
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
