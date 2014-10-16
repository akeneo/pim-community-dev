<?php

namespace Pim\Bundle\DataGridBundle\Datagrid\Product;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;
use Pim\Bundle\FilterBundle\Filter\ProductFilterUtility;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

/**
 * Filters configurator for product grid
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FiltersConfigurator implements ConfiguratorInterface
{
    /**
     * @var DatagridConfiguration
     */
    protected $configuration;

    /**
     * @var ConfigurationRegistry
     */
    protected $registry;

    /**
     * @var RequestParameters
     */
    protected $requestParameters;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var Request
     */
    protected $request;

    /**
     * Constructor
     * 
     * @param ConfigurationRegistry $registry
     * @param RequestParameters $requestParameters
     * @param RouterInterface $router
     */
    public function __construct(
        ConfigurationRegistry $registry,
        RequestParameters $requestParameters,
        RouterInterface $router
    ) {
        $this->registry = $registry;
        $this->requestParameters = $requestParameters;
        $this->router = $router;
    }

    /**
     * Set the current request
     * 
     * @param Request $request
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(DatagridConfiguration $configuration)
    {
        $this->configuration = $configuration;
        $appliedFilters = array_keys($this->requestParameters->get('_filter'));
        $path = sprintf('[source][%s]', ContextConfigurator::USEABLE_ATTRIBUTES_KEY);
        $attributes = $this->configuration->offsetGetByPath($path);
        $attributes = ($attributes === null) ? [] : $attributes;
        $getAllFilters = $this->request->get('_get_all_filters');

        $displayedFilters = [];
        foreach ($attributes as $attributeCode => $attribute) {
            $showFilter        = $attribute['useableAsGridFilter'];
            $attributeType     = $attribute['attributeType'];
            $attributeTypeConf = $this->registry->getConfiguration($attributeType);

            if ($showFilter && (!$attributeTypeConf || !isset($attributeTypeConf['filter']))) {
                throw new \LogicException(
                    sprintf(
                        'Attribute type %s must be configured to allow grid filter on attribute %s',
                        $attributeType,
                        $attributeCode
                    )
                );
            }

            if ($showFilter && $attributeTypeConf && isset($attributeTypeConf['filter'])) {

                $filterConfig = $attributeTypeConf['filter'];
                $filterConfig = $filterConfig + array(
                    ProductFilterUtility::DATA_NAME_KEY => $attributeCode,
                    'label'                             => $attribute['label'],
                    'enabled'                           => ($attributeType === 'pim_catalog_identifier'),
                    'order'                             => $attribute['sortOrder'],
                    'group'                             => $attribute['group'],
                    'groupOrder'                        => $attribute['groupOrder']
                );

                if ($attributeType === 'pim_catalog_metric') {
                    $filterConfig['family'] = $attribute['metricFamily'];
                }

                if (($getAllFilters || in_array($attributeCode, $appliedFilters)) || $filterConfig['enabled']) {
                    $displayedFilters[$attributeCode] = $filterConfig;
                }
            }
        }
        $this->sortFilters($displayedFilters);

        foreach ($displayedFilters as $attributeCode => $filterConfig) {
            $this->configuration->offsetSetByPath(
                sprintf('%s[%s]', FilterConfiguration::COLUMNS_PATH, $attributeCode),
                $filterConfig
            );
        }

        $this->configuration->offsetSetByPath(
            '[options][metadataUrl]', 
            $this->router->generate(
                'pim_datagrid_loadmetadata',
                [
                    'dataLocale' => $this->configuration->offsetGetByPath('[source][locale_code]'),
                    'alias' => $this->configuration->offsetGetByPath('[name]')
                ]
            )
        );
    }

    /**
     * Sort filters by group and attribute sort order
     *
     * @param array &$filters
     *
     * @return null
     */
    protected function sortFilters(&$filters)
    {
        uasort(
            $filters,
            function ($first, $second) {
                if ($first['groupOrder'] === null || $second['groupOrder'] === null) {
                    return $first['groupOrder'] === $second['groupOrder'] ?
                        0 : ($first['groupOrder'] === null ? 1 : -1);
                }

                if ($first['groupOrder'] === $second['groupOrder']) {
                    return $first['order'] > $second['order'] ? 1 : -1;
                }

                return $first['groupOrder'] > $second['groupOrder'] ? 1 : -1;
            }
        );
    }
}
