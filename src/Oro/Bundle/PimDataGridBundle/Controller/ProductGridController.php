<?php

declare(strict_types=1);

namespace Oro\Bundle\PimDataGridBundle\Controller;

use Akeneo\UserManagement\Bundle\Context\UserContext;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataIterableObject;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Oro\Bundle\PimDataGridBundle\Extension\Filter\FilterExtension;
use Oro\Bundle\PimDataGridBundle\Manager\DatagridViewManager;
use Oro\Bundle\PimDataGridBundle\Query\ListAttributesUseableInProductGrid;
use Oro\Bundle\PimDataGridBundle\Query\ListProductGridAvailableColumnGroups;
use Oro\Bundle\PimDataGridBundle\Query\ListProductGridAvailableColumns;
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
    /** @var ListAttributesUseableInProductGrid */
    private $listAttributesQuery;

    /** @var ConfiguratorInterface */
    private $filtersConfigurator;

    /** @var FilterExtension */
    private $filterExtension;

    /** @var UserContext */
    private $userContext;

    /** @var DatagridViewManager */
    private $datagridViewManager;

    /** @var ListProductGridAvailableColumns */
    private $listAvailableColumnsQuery;

    /** @var ListProductGridAvailableColumnGroups */
    private $listAvailableColumnGroupsQuery;

    /**
     * @param ListAttributesUseableInProductGrid   $listAttributesQuery
     * @param ConfiguratorInterface                $filtersConfigurator
     * @param FilterExtension                      $filterExtension
     * @param UserContext                          $userContext
     * @param DatagridViewManager                  $datagridViewManager
     * @param ListProductGridAvailableColumns      $listAvailableColumnsQuery
     * @param ListProductGridAvailableColumnGroups $listAvailableColumnGroupsQuery
     */
    public function __construct(
        ListAttributesUseableInProductGrid $listAttributesQuery,
        ConfiguratorInterface $filtersConfigurator,
        FilterExtension $filterExtension,
        UserContext $userContext,
        DatagridViewManager $datagridViewManager,
        ListProductGridAvailableColumns $listAvailableColumnsQuery,
        ListProductGridAvailableColumnGroups $listAvailableColumnGroupsQuery
    ) {
        $this->listAttributesQuery = $listAttributesQuery;
        $this->filtersConfigurator = $filtersConfigurator;
        $this->filterExtension = $filterExtension;
        $this->userContext = $userContext;
        $this->datagridViewManager = $datagridViewManager;
        $this->listAvailableColumnsQuery = $listAvailableColumnsQuery;
        $this->listAvailableColumnGroupsQuery = $listAvailableColumnGroupsQuery;
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
        $search = (string) $request->get('search', '');
        $locale = $request->get('locale', null);
        $user = $this->userContext->getUser();

        if (null == $locale) {
            $locale = $user->getCatalogLocale()->getCode();
        }

        $attributes = $this->listAttributesQuery->fetch($locale, $page, $search, $user->getId());
        $attributesAsFilters = empty($attributes) ? [] : $this->formatAttributesAsFilters($attributes);

        return new JsonResponse($attributesAsFilters);
    }

    /**
     * Get the list of the available columns for the product grid.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getAvailableColumnsAction(Request $request): JsonResponse
    {
        $page = (int) $request->get('page', 1);
        $locale = $request->get('locale', null);
        $search = $request->get('search', '');
        $group = $request->get('attribute_group', '');
        $user = $this->userContext->getUser();

        if (null == $locale) {
            $locale = $user->getCatalogLocale()->getCode();
        }

        $availableColumns = $this->listAvailableColumnsQuery->fetch($locale, $page, $group, $search, $user->getId());

        return new JsonResponse($availableColumns);
    }

    /**
     * Get the list of the groups of available columns for the product grid.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getAvailableColumnsGroupsAction(Request $request): JsonResponse
    {
        $locale = $request->get('locale', null);
        $user = $this->userContext->getUser();

        if (null == $locale) {
            $locale = $user->getCatalogLocale()->getCode();
        }

        $columnGroups = $this->listAvailableColumnGroupsQuery->fetch($locale, $user->getId());

        return new JsonResponse($columnGroups);
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
            $attribute['sortOrder'] = $attribute['order'];
            $attribute['useableAsGridFilter'] = true;

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
