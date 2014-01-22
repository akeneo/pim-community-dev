<?php

namespace Pim\Bundle\DataGridBundle\Datagrid\Flexible;

use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;

/**
 * Columns configurator for association product grid
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationProductColumnsConfigurator extends ColumnsConfigurator
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        parent::configure();

        $propertiesColumns = $this->configuration->offsetGetByPath(
            sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY)
        );

        $editableColumns = array();
        foreach ($propertiesColumns as $columnCode => $columnData) {
            if (isset($columnData['editable'])) {
                $editableColumns[$columnCode] = $columnData;
                unset($propertiesColumns[$columnCode]);
            }
        }

        $columns = $editableColumns + $propertiesColumns;
        $this->configuration->offsetSetByPath(sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY), $columns);
    }
}
