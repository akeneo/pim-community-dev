<?php

namespace Pim\Bundle\DataGridBundle\Datagrid\Product;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
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
     * @param array
     */
    protected $axisColumns;

    /**
     * @param DatagridConfiguration $configuration the grid config
     * @param ConfigurationRegistry $registry      the conf registry
     * @param Group                 $group         the current group
     */
    public function __construct(
        DatagridConfiguration $configuration,
        ConfigurationRegistry $registry,
        Group $group
    ) {
        parent::__construct($configuration, $registry);

        $this->group = $group;
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->preparePropertiesColumns();
        $this->prepareAttributesColumns();
        $this->prepareAxisColumns();
        $this->sortColumns();
        $this->addColumns();
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareAxisColumns()
    {
        $path = sprintf('[source][%s]', ContextConfigurator::USEABLE_ATTRIBUTES_KEY);
        $attributes = $this->configuration->offsetGetByPath($path);
        $axisCodes = array_map(
            function ($attribute) {
                return $attribute->getCode();
            },
            $this->group->getAttributes()->toArray()
        );
        $this->axisColumns = array();

        foreach ($attributes as $attributeCode => $attribute) {
            $attributeType     = $attribute['attributeType'];
            $attributeTypeConf = $this->registry->getConfiguration($attributeType);

            if ($attributeTypeConf && $attributeTypeConf['column']) {
                if (in_array($attributeCode, $axisCodes)) {
                    $columnConfig = $attributeTypeConf['column'];
                    $columnConfig = $columnConfig + array(
                        'label' => $attribute['label'],
                    );
                    $this->axisColumns[$attributeCode] = $columnConfig;
                }
            }
        }
    }

    /**
     * Sort the columns
     *
     * @return null
     */
    protected function sortColumns()
    {
        $this->displayedColumns = $this->editableColumns + $this->primaryColumns + $this->identifierColumn
            + $this->axisColumns + $this->propertiesColumns;
    }
}
