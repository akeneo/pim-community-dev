<?php

declare(strict_types=1);

namespace Pim\Bundle\DataGridBundle\Controller;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataIterableObject;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\FiltersConfigurator;
use Pim\Bundle\DataGridBundle\Extension\Filter\FilterExtension;
use Pim\Bundle\DataGridBundle\Query\ListAttributesQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Endpoints specific to the product grid.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductGridController
{
    /** @var ListAttributesQuery */
    private $listAttributesQuery;

    /** @var FiltersConfigurator */
    private $filtersConfigurator;

    /** @var FilterExtension */
    private $filterExtension;

    /**
     * @param ListAttributesQuery $listAttributesQuery
     * @param FiltersConfigurator $filtersConfigurator
     * @param FilterExtension     $filterExtension
     */
    public function __construct(
        ListAttributesQuery $listAttributesQuery,
        FiltersConfigurator $filtersConfigurator,
        FilterExtension $filterExtension
    ) {
        $this->listAttributesQuery = $listAttributesQuery;
        $this->filtersConfigurator = $filtersConfigurator;
        $this->filterExtension = $filterExtension;
    }

    /**
     * Get a paginated list of the attributes usable as filters for the product grid.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getAttributesFiltersAction(Request $request): JsonResponse
    {
        $page = (int) $request->get('page', 1);
        $locale = $request->get('locale', 'en_US'); // TODO: get user default locale
        $search = (string) $request->get('search', '');

        $attributes = $this->listAttributesQuery->fetch($locale, $page, $search);
        $attributesAsFilters = empty($attributes) ? [] : $this->formatAttributesAsFilters($attributes);

        return new JsonResponse($attributesAsFilters);
    }

    /**
     * Format a list of attributes as filters using the product-grid configuration
     *
     * @param array $attributes
     *
     * @return array
     */
    private function formatAttributesAsFilters(array $attributes): array
    {
        $configurationAttributes = [];
        foreach ($attributes as $index => $attribute) {
            $attribute['group'] = $attribute['groupLabel'];
            $attribute['order'] = $attribute['sortOrder'];

            $configurationAttributes[$attribute['code']] = $attribute;
        }

        $productGridConfiguration = DatagridConfiguration::createNamed('product-grid', [
            ConfiguratorInterface::SOURCE_KEY => [
                ConfiguratorInterface::USEABLE_ATTRIBUTES_KEY => $configurationAttributes
            ],
            FilterConfiguration::FILTERS_KEY => [],
        ]);

        $this->filtersConfigurator->configure($productGridConfiguration);

        $productGridMetadata = MetadataIterableObject::createNamed('product-grid', ['filters' => []]);
        $this->filterExtension->visitMetadata($productGridConfiguration, $productGridMetadata);

        $attributesAsFilters = $productGridMetadata->offsetGet('filters');

        return $attributesAsFilters;
    }
}
