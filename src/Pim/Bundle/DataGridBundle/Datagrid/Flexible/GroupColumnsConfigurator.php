<?php

namespace Pim\Bundle\DataGridBundle\Datagrid\Flexible;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Pim\Bundle\CatalogBundle\Entity\Group;

/**
 * Columns configurator for products grid (used to associate products to groups)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupColumnsConfigurator extends ColumnsConfigurator
{
    /**
     * @var Group
     */
    protected $group;

    /**
     * @param DatagridConfiguration $configuration the grid config
     * @param ConfigurationRegistry $registry      the conf registry
     * @param array                 $attributes    the attributes
     * @param Group                 $group         the current group
     */
    public function __construct(DatagridConfiguration $configuration, ConfigurationRegistry $registry, $attributes, Group $group)
    {
        parent::__construct($configuration, $registry, $attributes);
        $this->group = $group;
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $propertiesColumns = $this->configuration->offsetGetByPath(sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY));

        $editableColumn = array();
        foreach ($propertiesColumns as $columnCode => $columnData) {
            if (isset($columnData['editable'])) {
                $editableColumn[$columnCode]= $columnData;
                unset($propertiesColumns[$columnCode]);
            }
        }
        $identifierColumn = array();
        $axisCodes = array_map(
            function ($attribute) {
                return $attribute->getCode();
            },
            $this->group->getAttributes()->toArray()
        );
        $axisColumns = array();

        foreach ($this->attributes as $attributeCode => $attribute) {
            $attributeType     = $attribute->getAttributeType();
            $attributeTypeConf = $this->registry->getConfiguration($attributeType);

            if ($attributeTypeConf && $attributeTypeConf['column']) {
                if ($attributeType === 'pim_catalog_identifier') {
                    $columnConfig = $attributeTypeConf['column'];
                    $columnConfig = $columnConfig + array(
                        'label' => $attribute->getLabel(),
                    );
                    $identifierColumn[$attributeCode]= $columnConfig;
                } elseif (in_array($attributeCode, $axisCodes)) {
                    $columnConfig = $attributeTypeConf['column'];
                    $columnConfig = $columnConfig + array(
                        'label' => $attribute->getLabel(),
                    );
                    $axisColumns[$attributeCode]= $columnConfig;
                }
            }
        }

        $columns = $editableColumn + $identifierColumn + $axisColumns + $propertiesColumns;
        $this->configuration->offsetSetByPath(sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY), $columns);
    }
}
