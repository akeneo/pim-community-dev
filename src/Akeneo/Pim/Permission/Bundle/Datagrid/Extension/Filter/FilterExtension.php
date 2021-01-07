<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Datagrid\Extension\Filter;

use Akeneo\Pim\Permission\Bundle\Entity\Repository\AttributeGroupAccessRepository;
use Akeneo\Pim\Permission\Bundle\Entity\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Permission\Bundle\User\UserContext;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Datagrid\DatagridViewTypes;
use Doctrine\Common\Persistence\ObjectRepository;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataIterableObject;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration;
use Oro\Bundle\PimDataGridBundle\Datasource\DatasourceAdapterResolverInterface;
use Oro\Bundle\PimDataGridBundle\Extension\Filter\FilterExtension as BaseFilterExtension;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class FilterExtension extends BaseFilterExtension
{
    protected AttributeRepositoryInterface $attributeRepository;
    protected AttributeGroupAccessRepository $accessRepository;
    protected UserContext $userContext;
    protected ObjectRepository $datagridViewRepository;
    protected ObjectRepository $projectRepository;

    public function __construct(
        RequestParameters $requestParams,
        TranslatorInterface $translator,
        DatasourceAdapterResolverInterface $adapterResolver,
        AttributeRepositoryInterface $attributeRepository,
        AttributeGroupAccessRepository $accessRepository,
        UserContext $userContext,
        ObjectRepository $datagridViewRepository,
        ObjectRepository $projectRepository
    ) {
        parent::__construct($requestParams, $translator, $adapterResolver);

        $this->attributeRepository = $attributeRepository;
        $this->accessRepository = $accessRepository;
        $this->userContext = $userContext;
        $this->datagridViewRepository = $datagridViewRepository;
        $this->projectRepository = $projectRepository;
    }

    /**
     * {@inheritdoc}
     *
     * We override this method to add a new grid that can use the category filter
     */
    protected function getCategoryFilterConfig($gridName)
    {
        $gridConfigs = [
            'published-product-grid' => [
                'type'      => 'published_product_category',
                'data_name' => 'category'
            ]
        ];

        return isset($gridConfigs[$gridName]) ? $gridConfigs[$gridName] : parent::getCategoryFilterConfig($gridName);
    }

    /**
     * {@inheritdoc}
     *
     * This method fills the 'filters' and 'state' parameters for the datagrid to tell the grid what filters
     * it should display. We simply remove the attributes the user can't see.
     *
     * We do that here because the ContextConfigurator didn't remove them to allow the PQB to be filtered.
     */
    public function visitMetadata(DatagridConfiguration $config, MetadataIterableObject $data)
    {
        parent::visitMetadata($config, $data);

        $enabled = $config['options']['removeFiltersNotUsableInGrid'] ?? true;

        if (false === $enabled) {
            return;
        }

        $filtersState = $data->offsetGet('state');
        $filtersState = isset($filtersState['filters']) ? $filtersState['filters'] : [];
        $filtersMetaData = $data->offsetGet('filters');

        $grantedAttributeGroupIds = $this->accessRepository->getGrantedAttributeGroupIds(
            $this->userContext->getUser(),
            Attributes::VIEW_ATTRIBUTES
        );

        $grantedAttributeCodes = $this->attributeRepository->findAttributeCodesUsableInGrid($grantedAttributeGroupIds);
        $attributeCodes = $this->attributeRepository->findAttributeCodes();

        foreach ($filtersState as $code => $filter) {
            if (in_array($code, $attributeCodes) && !in_array($code, $grantedAttributeCodes)) {
                unset($filtersState[$code]);
            }
        }

        foreach ($filtersMetaData as $code => $filter) {
            if (in_array($filter['name'], $attributeCodes) && !in_array($filter['name'], $grantedAttributeCodes)) {
                unset($filtersMetaData[$code]);
            }
        }

        $data->offsetSet('state', ['filters' => $filtersState]);
        $data->offsetSet('filters', $filtersMetaData);
    }

    /**
     * {@inheritdoc}
     *
     * A user can't update fixed filters of a view linked to a project.
     * That's what we do here, we force the filters of a view if it's linked to a project.
     */
    protected function getValuesToApply(DatagridConfiguration $config)
    {
        $filtersToApply = [];

        $requestParams = $this->requestParams->get('_parameters');
        $datagridViewId = isset($requestParams['view']['id']) ? $requestParams['view']['id'] : null;

        $datagridViewFilters = [];

        if (null !== $datagridViewId) {
            $datagridView = $this->datagridViewRepository->find($datagridViewId);
            if (null !== $datagridView && DatagridViewTypes::PROJECT_VIEW === $datagridView->getType()) {
                $project = $this->projectRepository->findOneBy(['datagridView' => $datagridView]);
                // TODO: Check if user has permission on the project
                if (null !== $project) {
                    parse_str($datagridView->getFilters(), $datagridViewFilters);
                    $datagridViewFilters = $datagridViewFilters['f'];
                }
            }
        }

        $grantedAttributeGroupIds = $this->accessRepository->getGrantedAttributeGroupIds(
            $this->userContext->getUser(),
            Attributes::VIEW_ATTRIBUTES
        );
        $grantedAttributeCodes = $this->attributeRepository->findAttributeCodesUsableInGrid($grantedAttributeGroupIds);
        $attributeCodes = $this->attributeRepository->findAttributeCodes();

        $useableFilters = $config->offsetGetByPath(Configuration::COLUMNS_PATH);
        $defaultFilters = $config->offsetGetByPath(Configuration::DEFAULT_FILTERS_PATH, []);
        $filterBy = $this->requestParams->get(self::FILTER_ROOT_PARAM) ?: $defaultFilters;

        // Allow to empty a filter if the user has access to this filter
        foreach ($datagridViewFilters as $column => $value) {
            $isGrantedAttribute = in_array($column, $grantedAttributeCodes);
            $isAField = !in_array($column, $attributeCodes);
            $isEmptyFilter = !array_key_exists($column, $filterBy);

            if (($isGrantedAttribute || $isAField) && $isEmptyFilter) {
                unset($datagridViewFilters[$column]);
            }
        }

        $filterBy = array_replace($datagridViewFilters, $filterBy);

        foreach ($filterBy as $column => $value) {
            if (isset($useableFilters[$column]) || 'category' === $column) {
                $filtersToApply[$column] = $value;
            }
        }

        return $filtersToApply;
    }
}
