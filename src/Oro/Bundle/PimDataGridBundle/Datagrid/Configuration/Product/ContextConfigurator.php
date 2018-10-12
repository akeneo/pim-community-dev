<?php

declare(strict_types=1);

namespace Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product;

use Akeneo\Pim\Enrichment\Component\Product\Repository\GroupRepositoryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Extension\Toolbar\ToolbarExtension;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Oro\Bundle\PimDataGridBundle\Extension\Pager\PagerExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Context configurator for product grid, it allows to inject all dynamic configuration as user grid config,
 * attributes config, current locale
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ContextConfigurator implements ConfiguratorInterface
{
    /** @staticvar string */
    const PRODUCT_STORAGE_KEY = 'product_storage';

    /** @staticvar string */
    const CURRENT_GROUP_ID_KEY = 'current_group_id';

    /** @staticvar string */
    const ASSOCIATION_TYPE_ID_KEY = 'association_type_id';

    /** @staticvar string */
    const CURRENT_PRODUCT_KEY = 'current_product';

    /** @staticvar string */
    const USER_CONFIG_ALIAS_KEY = 'user_config_alias';

    /** @var DatagridConfiguration */
    protected $configuration;

    /** @var ObjectRepository */
    protected $objectRepository;

    /** @var RequestParameters */
    protected $requestParams;

    /** @var UserContext */
    protected $userContext;

    /** @var RequestStack */
    protected $requestStack;

    /** @var ObjectManager */
    protected $objectManager;

    /** @var GroupRepositoryInterface */
    protected $productGroupRepository;

    /**
     * @param ObjectRepository             $objectRepository
     * @param RequestParameters            $requestParams
     * @param UserContext                  $userContext
     * @param ObjectManager                $objectManager
     * @param GroupRepositoryInterface     $productGroupRepository
     * @param RequestStack                 $requestStack
     */
    public function __construct(
        ObjectRepository $objectRepository,
        RequestParameters $requestParams,
        UserContext $userContext,
        ObjectManager $objectManager,
        GroupRepositoryInterface $productGroupRepository,
        RequestStack $requestStack
    ) {
        $this->objectRepository = $objectRepository;
        $this->requestParams = $requestParams;
        $this->userContext = $userContext;
        $this->objectManager = $objectManager;
        $this->productGroupRepository = $productGroupRepository;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(DatagridConfiguration $configuration)
    {
        $this->configuration = $configuration;
        $this->addProductStorage();
        $this->addLocaleCode();
        $this->addScopeCode();
        $this->addRepositoryParameters();
        $this->addCurrentGroupId();
        $this->addAssociationTypeId();
        $this->addCurrentProduct();
        $this->addDisplayedColumnCodes();
        $this->addPaginationConfig();
    }

    /**
     * @return Request|null
     */
    protected function getRequest(): ?Request
    {
        return $this->requestStack->getCurrentRequest();
    }

    /**
     * @param string $key the configuration key
     *
     * @return string
     */
    protected function getSourcePath($key)
    {
        return sprintf(self::SOURCE_PATH, $key);
    }

    /**
     * Inject used product storage in the datagrid configuration
     */
    protected function addProductStorage()
    {
        $path = $this->getSourcePath(self::PRODUCT_STORAGE_KEY);
        $this->configuration->offsetSetByPath($path, 'doctrine/orm');
    }

    /**
     * Inject current locale code in the datagrid configuration
     */
    protected function addLocaleCode()
    {
        $localeCode = $this->getCurrentLocaleCode();
        $path = $this->getSourcePath(self::DISPLAYED_LOCALE_KEY);
        $this->configuration->offsetSetByPath($path, $localeCode);
    }

    /**
     * Inject current scope code in the datagrid configuration
     */
    protected function addScopeCode()
    {
        $scopeCode = $this->getCurrentScopeCode();
        $path = $this->getSourcePath(self::DISPLAYED_SCOPE_KEY);
        $this->configuration->offsetSetByPath($path, $scopeCode);
    }

    /**
     * Inject current group id in the datagrid configuration
     */
    protected function addCurrentGroupId()
    {
        $groupId = $this->getProductGroupId();
        $path = $this->getSourcePath(self::CURRENT_GROUP_ID_KEY);
        $this->configuration->offsetSetByPath($path, $groupId);
    }

    /**
     * Inject current association type id in the datagrid configuration
     */
    protected function addAssociationTypeId()
    {
        $path = $this->getSourcePath(self::ASSOCIATION_TYPE_ID_KEY);
        $params = $this->requestParams->get(RequestParameters::ADDITIONAL_PARAMETERS);
        if (isset($params['associationType']) && null !== $params['associationType']) {
            $typeId = $params['associationType'];
        } else {
            $typeId = $this->requestParams->get('associationType', null);
        }
        $this->configuration->offsetSetByPath($path, $typeId);
    }

    /**
     * Inject current product in the datagrid configuration
     */
    protected function addCurrentProduct()
    {
        $path = $this->getSourcePath(self::CURRENT_PRODUCT_KEY);
        $id = $this->requestParams->get('product', null);
        $object = null !== $id ? $this->objectRepository->find($id) : null;

        $this->configuration->offsetSetByPath($path, $object);
    }

    /**
     * Inject requested repository parameters in the datagrid configuration
     */
    protected function addRepositoryParameters()
    {
        $path = $this->getSourcePath(self::REPOSITORY_PARAMETERS_KEY);
        $repositoryParams = $this->configuration->offsetGetByPath($path, null);

        if ($repositoryParams) {
            $params = [];
            foreach ($repositoryParams as $paramName) {
                $params[$paramName] = $this->requestParams->get($paramName, $this->getRequest()->get($paramName, null));
            }
            $this->configuration->offsetSetByPath($path, $params);
        }
    }

    /**
     * Inject displayed columns in the datagrid configuration
     */
    protected function addDisplayedColumnCodes()
    {
        $userColumns = $this->getUserGridColumns();

        if ($userColumns) {
            $path = $this->getSourcePath(self::DISPLAYED_COLUMNS_KEY);
            $this->configuration->offsetSetByPath($path, $userColumns);
        }
    }

    /**
     * Get current locale from datagrid parameters, then request parameters, then user config
     *
     * @return string
     */
    protected function getCurrentLocaleCode()
    {
        $dataLocale = $this->requestParams->get('dataLocale', null);
        if (!$dataLocale) {
            $dataLocale = $this->getRequest()->get('dataLocale', null);
        }
        if (!$dataLocale && $locale = $this->userContext->getUser()->getCatalogLocale()) {
            $dataLocale = $locale->getCode();
        }

        return $dataLocale;
    }

    /**
     * @return int|null
     */
    protected function getProductGroupId()
    {
        $productGroupId = null;
        if (null !== $productGroup = $this->getRequest()->get('group', null)) {
            $productGroupId = $productGroup->getId();
        }
        if (null === $productGroupId) {
            $productGroupId = $this->requestParams->get('currentGroup', null);
        }

        return $productGroupId;
    }

    /**
     * Get current scope from datagrid parameters, then user config
     *
     * @return string
     */
    protected function getCurrentScopeCode()
    {
        $filterValues = $this->requestParams->get('_filter');
        $currentScopeCode = null;

        if (isset($filterValues['scope']['value'])) {
            $currentScopeCode = $filterValues['scope']['value'];
        }

        if (null === $currentScopeCode) {
            $requestFilters = $this->getRequest()->get('filters');
            if (isset($requestFilters['scope']['value'])) {
                $currentScopeCode = $requestFilters['scope']['value'];
            }
        }

        if (null === $currentScopeCode) {
            $channel = $this->userContext->getUser()->getCatalogScope();
            $currentScopeCode = $channel->getCode();
        }

        return $currentScopeCode;
    }

    /**
     * Get user configured datagrid columns
     *
     * @return string[]
     */
    protected function getUserGridColumns()
    {
        $params = $this->requestParams->get(RequestParameters::ADDITIONAL_PARAMETERS);

        if (isset($params['view']) && isset($params['view']['columns'])) {
            return explode(',', $params['view']['columns']);
        }

        return [];
    }

    /**
     * Inject requested _per_page parameters in the datagrid configuration
     */
    protected function addPaginationConfig()
    {
        $pager = $this->requestParams->get(PagerExtension::PAGER_ROOT_PARAM);

        $defaultPerPage = $this->configuration->offsetGetByPath(
            ToolbarExtension::PAGER_DEFAULT_PER_PAGE_OPTION_PATH,
            25
        );
        $itemsPerPage = isset($pager[PagerExtension::PER_PAGE_PARAM]) ? (int)$pager[PagerExtension::PER_PAGE_PARAM] : $defaultPerPage;

        $this->configuration->offsetSetByPath($this->getSourcePath(PagerExtension::PER_PAGE_PARAM), $itemsPerPage);

        $currentPage = isset($pager[PagerExtension::PAGE_PARAM]) ? (int)$pager[PagerExtension::PAGE_PARAM] : 1;
        $from = ($currentPage - 1) * $itemsPerPage;
        $this->configuration->offsetSetByPath($this->getSourcePath('from'), $from);
    }
}
