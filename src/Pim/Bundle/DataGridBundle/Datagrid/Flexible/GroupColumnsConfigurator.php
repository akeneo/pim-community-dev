<?php

namespace Pim\Bundle\DataGridBundle\Datagrid\Flexible;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;

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
        $identifierColumn  = array();

        foreach ($this->attributes as $attributeCode => $attribute) {
            $showColumn        = $attribute->isUseableAsGridColumn();
            $attributeType     = $attribute->getAttributeType();
            $attributeTypeConf = $this->registry->getConfiguration($attributeType);
            if ($showColumn && $attributeTypeConf && $attributeTypeConf['column'] and $attributeType === 'pim_catalog_identifier') {
                $columnConfig = $attributeTypeConf['column'];
                $columnConfig = $columnConfig + array(
                    'label' => $attribute->getLabel(),
                );
                $identifierColumn[$attributeCode]= $columnConfig;
            }
        }

        $columns = $editableColumn + $identifierColumn + $propertiesColumns;
        $this->configuration->offsetSetByPath(sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY), $columns);
    }
}
