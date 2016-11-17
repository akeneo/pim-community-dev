<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Bundle\Datagrid;

use Akeneo\ActivityManager\Component\Model\DatagridViewTypes;
use Akeneo\ActivityManager\Component\Repository\AttributeRepositoryInterface;
use Akeneo\ActivityManager\Component\Repository\ProjectRepositoryInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataObject;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration;
use Pim\Bundle\DataGridBundle\Datasource\DatasourceAdapterResolver;
use Pim\Bundle\DataGridBundle\Repository\DatagridViewRepositoryInterface;
use PimEnterprise\Bundle\DataGridBundle\Extension\Filter\FilterExtension as EnterpriseFilterExtension;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AttributeGroupAccessRepository;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Override of the Enterprise FilterExtension of the datagrid.
 *
 * We override it to change the behavior of the visitMetadata method, to remove attributes the user
 * doesn't have access to.
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class FilterExtension extends EnterpriseFilterExtension
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var AttributeGroupAccessRepository */
    protected $accessRepository;

    /** @var UserContext */
    protected $userContext;

    /** @var DatagridViewRepositoryInterface */
    protected $datagridViewRepository;

    /** @var ProjectRepositoryInterface */
    protected $projectRepository;

    /**
     * @param RequestParameters               $requestParams
     * @param TranslatorInterface             $translator
     * @param DatasourceAdapterResolver       $adapterResolver
     * @param AttributeRepositoryInterface    $attributeRepository
     * @param AttributeGroupAccessRepository  $accessRepository
     * @param UserContext                     $userContext
     * @param DatagridViewRepositoryInterface $datagridViewRepository
     * @param ProjectRepositoryInterface      $projectRepository
     */
    public function __construct(
        RequestParameters $requestParams,
        TranslatorInterface $translator,
        DatasourceAdapterResolver $adapterResolver,
        AttributeRepositoryInterface $attributeRepository,
        AttributeGroupAccessRepository $accessRepository,
        UserContext $userContext,
        DatagridViewRepositoryInterface $datagridViewRepository,
        ProjectRepositoryInterface $projectRepository
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
     * This method fills the 'filters' and 'state' parameters for the datagrid to tell the grid what filters
     * it should display. We simply remove the attributes the user can't see.
     *
     * We do that here because the ContextConfigurator didn't remove them to allow the PQB to be filtered.
     */
    public function visitMetadata(DatagridConfiguration $config, MetadataObject $data)
    {
        parent::visitMetadata($config, $data);

        $filtersState = $data->offsetGet('state');
        $filtersState = isset($filtersState['filters']) ? $filtersState['filters'] : [];
        $filtersMetaData = $data->offsetGet('filters');

        $grantedAttributeGroupIds = $this->accessRepository->getGrantedAttributeGroupIds(
            $this->userContext->getUser(),
            Attributes::VIEW_ATTRIBUTES
        );

        $grantedAttributeCodes = $this->attributeRepository->findAttributeCodesUseableInGrid($grantedAttributeGroupIds);
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

        $data->offsetSet('state', ['filters' => $filtersState])
            ->offsetSet('filters', $filtersMetaData);
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

        $useableFilters = $config->offsetGetByPath(Configuration::COLUMNS_PATH);
        $defaultFilters = $config->offsetGetByPath(Configuration::DEFAULT_FILTERS_PATH, []);

        $filterBy = $this->requestParams->get(self::FILTER_ROOT_PARAM) ?: $defaultFilters;
        $filterBy = array_replace($filterBy, $datagridViewFilters);

        foreach ($filterBy as $column => $value) {
            if (isset($useableFilters[$column]) || 'category' === $column) {
                $filtersToApply[$column] = $value;
            }
        }

        return $filtersToApply;
    }
}
