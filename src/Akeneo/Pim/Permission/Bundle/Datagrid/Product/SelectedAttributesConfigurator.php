<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Datagrid\Product;

use Akeneo\Pim\Permission\Bundle\Entity\Repository\AttributeGroupAccessRepository;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Inject in the product grid configuration only the attributes selected as filter or as column.
 * It is done for performance reason, as loading all the attributes can be very time-consuming.
 *
 * These attributes (for selection and filtering) has to be paginated in a dedicated endpoint
 * (see Oro\Bundle\PimDataGridBundle\Controller\ProductGridController )
 *
 * @author Laurent Petard <laurent.petard@akeneo.com>
 */
class SelectedAttributesConfigurator implements ConfiguratorInterface
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var UserContext */
    private $userContext;

    /** @var RequestParameters */
    private $requestParams;

    /** @var RequestStack */
    private $requestStack;

    /** @var AttributeGroupAccessRepository */
    private $accessRepository;

    /** @var array */
    private $grantedGroupIds = [];

    /**
     * @param AttributeRepositoryInterface   $attributeRepository
     * @param UserContext                    $userContext
     * @param RequestParameters              $requestParams
     * @param RequestStack                   $requestStack
     * @param AttributeGroupAccessRepository $accessRepository
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        UserContext $userContext,
        RequestParameters $requestParams,
        RequestStack $requestStack,
        AttributeGroupAccessRepository $accessRepository
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->userContext = $userContext;
        $this->requestParams = $requestParams;
        $this->requestStack = $requestStack;
        $this->accessRepository = $accessRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(DatagridConfiguration $configuration)
    {
        $this->addAttributesIds($configuration);
        $this->addAttributesConfig($configuration);
    }

    /**
     * Inject attributes configurations in the product grid configuration
     *
     * @param DatagridConfiguration $configuration
     *
     * @return array
     */
    private function addAttributesConfig(DatagridConfiguration $configuration): void
    {
        $filterValues = array_merge($this->requestParams->get('_filter', []), $this->requestStack->getCurrentRequest()->get('filters', []));

        unset($filterValues['scope']);
        unset($filterValues['category']);
        $attributesUsedAsFilter = array_keys($filterValues);

        $userColumns = $configuration->offsetGetByPath(
            sprintf(self::SOURCE_PATH, self::DISPLAYED_COLUMNS_KEY), []
        );
        $baseColumns = array_keys($configuration->offsetGet('columns'));
        $attributesUsedAsColumn = array_diff($userColumns, $baseColumns);
        $filters = $this->userContext->getUser()->getProductGridFilters();

        $usedAttributeCodes = array_unique(array_merge($attributesUsedAsFilter, $attributesUsedAsColumn, $filters));
        $attributeIds = empty($usedAttributeCodes) ? [] : $this->getAttributeIdsUseableInGrid($usedAttributeCodes);

        $attributes = [];
        if (!empty($attributeIds)) {
            $currentLocale = $this->getCurrentLocaleCode();
            $attributes = $this->attributeRepository->getAttributesAsArray(true, $currentLocale, $attributeIds);
        }

        $path = $this->getSourcePath(self::USEABLE_ATTRIBUTES_KEY);
        $configuration->offsetSetByPath($path, $attributes);
    }

    /**
     * Inject the displayed attribute ids in the product grid configuration
     *
     * @param DatagridConfiguration $configuration
     */
    protected function addAttributesIds(DatagridConfiguration $configuration): void
    {
        $attributeCodes = [];
        $params = $this->requestParams->get(RequestParameters::ADDITIONAL_PARAMETERS);

        if (isset($params['view']) && isset($params['view']['columns'])) {
            $attributeCodes = explode(',', $params['view']['columns']);
        }

        $attributeIds = empty($attributeCodes) ? [] : $this->getAttributeIdsUseableInGrid($attributeCodes);

        $path = $this->getSourcePath(self::DISPLAYED_ATTRIBUTES_KEY);
        $configuration->offsetSetByPath($path, $attributeIds);
    }

    /**
     * @param string $key the configuration key
     *
     * @return string
     */
    private function getSourcePath($key): string
    {
        return sprintf(self::SOURCE_PATH, $key);
    }

    /**
     * Get current locale from datagrid parameters, then request parameters, then user config
     *
     * @return string|null
     */
    private function getCurrentLocaleCode(): ?string
    {
        $dataLocale = $this->requestParams->get('dataLocale', null);
        if (!$dataLocale) {
            $request = $this->requestStack->getCurrentRequest();
            $dataLocale = $request->get('dataLocale', null);
        }
        if (!$dataLocale && $locale = $this->userContext->getUser()->getCatalogLocale()) {
            $dataLocale = $locale->getCode();
        }

        return $dataLocale;
    }

    /**
     * @param array|null $attributeCodes
     *
     * @return array
     */
    private function getAttributeIdsUseableInGrid(array $attributeCodes = null): array
    {
        $groupIds = $this->getGrantedGroupIds();

        return $this->attributeRepository->getAttributeIdsUseableInGrid($attributeCodes, $groupIds);
    }

    /**
     * @return int[]
     */
    private function getGrantedGroupIds(): array
    {
        if (!$this->grantedGroupIds) {
            $result = $this->accessRepository
                ->getGrantedAttributeGroupQB($this->userContext->getUser(), Attributes::VIEW_ATTRIBUTES)
                ->getQuery()
                ->getArrayResult();

            $this->grantedGroupIds = array_map(
                function ($row) {
                    return $row['id'];
                },
                $result
            );
        }

        return $this->grantedGroupIds;
    }
}
