<?php

namespace Pim\Bundle\DataGridBundle\Datagrid\Flexible;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Pim\Bundle\DataGridBundle\Extension\Formatter\Property\FlexibleFieldProperty;
use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\FilterBundle\Filter\Flexible\FilterUtility;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;

/**
 * Filters configurator for flexible grid
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FiltersConfigurator implements ConfiguratorInterface
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
     * @param $string
     */
    protected $flexibleEntity;

    /**
     * @param DatagridConfiguration $configuration  the grid config
     * @param array                 $attributes     the attributes
     * @param string                $flexibleEntity the flexible entity FQCN
     */
    public function __construct(DatagridConfiguration $configuration, $attributes, $flexibleEntity)
    {
        $this->configuration  = $configuration;
        $this->attributes     = $attributes;
        $this->flexibleEntity = $flexibleEntity;
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        foreach ($this->attributes as $attributeCode => $attribute) {
            $showFilter    = $attribute->isUseableAsGridFilter();
            $attributeType = $attribute->getAttributeType();
            // TODO: to fix
            if (in_array($attributeType, array('pim_catalog_file', 'pim_catalog_image'))) {
                continue;
            }
            if ($showFilter) {
                $map         = FlexibleFieldProperty::$typeMatches;
                $backendType = $attribute->getBackendType();

                $filterType = isset(FlexibleFieldProperty::$typeMatches[$backendType])
                    ? $map[$backendType]['filter']
                    : $map[AbstractAttributeType::BACKEND_TYPE_TEXT]['filter'];

                $parentType = isset(FlexibleFieldProperty::$typeMatches[$backendType])
                    ? $map[$backendType]['parent_filter']
                    : $map[AbstractAttributeType::BACKEND_TYPE_TEXT]['parent_filter'];

                $filterConfig = array(
                    FilterUtility::TYPE_KEY        => $filterType,
                    FilterUtility::FEN_KEY         => $this->flexibleEntity,
                    FilterUtility::DATA_NAME_KEY   => $attributeCode,
                    FilterUtility::PARENT_TYPE_KEY => $parentType,
                    'label'                        => $attribute->getLabel(),
                    'enabled'                      => ($attributeType === 'pim_catalog_identifier')
                );

                if (isset($map[$backendType]['field_options'])) {
                    $filterConfig[FilterUtility::FORM_OPTIONS_KEY] = array(
                        'field_options' => $map[$backendType]['field_options']
                    );
                }

                if ($backendType === 'metric') {
                    $filterConfig['family'] = $attribute->getMetricFamily();
                }

                $this->configuration->offsetSetByPath(
                    sprintf('%s[%s]', FilterConfiguration::COLUMNS_PATH, $attributeCode),
                    $filterConfig
                );
            }
        }
    }
}

