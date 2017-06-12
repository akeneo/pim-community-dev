<?php

namespace Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\AkeneoStorageUtilsExtension;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Extension\Toolbar\ToolbarExtension;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Pim\Bundle\DataGridBundle\Extension\Pager\PagerExtension;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;

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
    const DISPLAYED_ATTRIBUTES_KEY = 'displayed_attribute_ids';

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

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var RequestParameters */
    protected $requestParams;

    /** @var UserContext */
    protected $userContext;

    /** @var Request */
    protected $request;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var ObjectManager */
    protected $objectManager;

    /** @var GroupRepositoryInterface */
    protected $productGroupRepository;

    /**
     * @param ProductRepositoryInterface   $productRepository
     * @param AttributeRepositoryInterface $attributeRepository
     * @param RequestParameters            $requestParams
     * @param UserContext                  $userContext
     * @param ObjectManager                $objectManager
     * @param GroupRepositoryInterface     $productGroupRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        AttributeRepositoryInterface $attributeRepository,
        RequestParameters $requestParams,
        UserContext $userContext,
        ObjectManager $objectManager,
        GroupRepositoryInterface $productGroupRepository
    ) {
        $this->productRepository = $productRepository;
        $this->attributeRepository = $attributeRepository;
        $this->requestParams = $requestParams;
        $this->userContext = $userContext;
        $this->objectManager = $objectManager;
        $this->productGroupRepository = $productGroupRepository;
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
        $this->addAttributesIds();
        $this->addAttributesConfig();
        $this->addPaginationConfig();
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
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
     * Inject the displayed attribute ids in the datagrid configuration
     */
    protected function addAttributesIds()
    {
        $attributeCodes = $this->getUserGridColumns();
        $attributeIds = $this->getAttributeIds($attributeCodes);

        $path = $this->getSourcePath(self::DISPLAYED_ATTRIBUTES_KEY);
        $this->configuration->offsetSetByPath($path, $attributeIds);
    }

    /**
     * Return attribute ids to add to the conf
     *
     * @param string[]|null $attributeCodes
     *
     * @return integer[]
     */
    protected function getAttributeIds($attributeCodes = null)
    {
        // as now only attributes usable as grid filters are usable in the grid,
        // we need to add the variant group axes (even if they are not usable as grid filter)
        // as usable in the grid (to be displayed in the columns)
        $attributeIds = array_merge(
            $this->getAttributeIdsUseableInGrid($attributeCodes),
            $this->getAttributeIdsFromProductGroupAxes()
        );

        return $attributeIds;
    }

    /**
     * Return usable attribute ids
     *
     * @param string[]|null $attributeCodes
     *
     * @return integer[]
     */
    protected function getAttributeIdsUseableInGrid($attributeCodes = null)
    {
        return $this->attributeRepository->getAttributeIdsUseableInGrid($attributeCodes);
    }

    /**
     * Inject used product storage in the datagrid configuration
     */
    protected function addProductStorage()
    {
        $storage = $this->getProductStorage();
        $path = $this->getSourcePath(self::PRODUCT_STORAGE_KEY);
        $this->configuration->offsetSetByPath($path, $storage);
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
        $product = null !== $id ? $this->productRepository->find($id) : null;
        $this->configuration->offsetSetByPath($path, $product);
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
                $params[$paramName] = $this->requestParams->get($paramName, $this->request->get($paramName, null));
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
     * Inject attributes configurations in the datagrid configuration
     */
    protected function addAttributesConfig()
    {
        $attributes = $this->getAttributesConfig();
        $path = $this->getSourcePath(self::USEABLE_ATTRIBUTES_KEY);
        $this->configuration->offsetSetByPath($path, $attributes);
    }

    /**
     * Get product storage (ORM/MongoDBODM)
     *
     * @return string
     */
    protected function getProductStorage()
    {
        $om = $this->objectManager;
        if ($om instanceof \Doctrine\ORM\EntityManagerInterface) {
            return AkeneoStorageUtilsExtension::DOCTRINE_ORM;
        }

        return AkeneoStorageUtilsExtension::DOCTRINE_MONGODB_ODM;
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
            $dataLocale = $this->request->get('dataLocale', null);
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
        if (null !== $productGroup = $this->request->get('group', null)) {
            $productGroupId = $productGroup->getId();
        }
        if (null === $productGroupId) {
            $productGroupId = $this->requestParams->get('currentGroup', null);
        }

        return $productGroupId;
    }

    /**
     * @return array
     */
    protected function getAttributeIdsFromProductGroupAxes()
    {
        $attributeIds = [];

        if (null !== $productGroupId = $this->getProductGroupId()) {
            $group = $this->productGroupRepository->find($productGroupId);
            if ($group->getType()->isVariant()) {
                foreach ($group->getAxisAttributes() as $axis) {
                    $attributeIds[] = $axis->getId();
                }
            }
        }

        return $attributeIds;
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
            $requestFilters = $this->request->get('filters');
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
     * Get attributes configuration for attribute that can be used in grid (as column or filter)
     *
     * @return array
     */
    protected function getAttributesConfig()
    {
        $attributeIds = $this->getAttributeIds();
        if (empty($attributeIds)) {
            return [];
        }

        $currentLocale = $this->getCurrentLocaleCode();
        $configuration = $this->attributeRepository->getAttributesAsArray(true, $currentLocale, $attributeIds);

        return $configuration;
    }

    /**
     * Get user configured datagrid columns
     *
     * @return null|string[]
     */
    protected function getUserGridColumns()
    {
        $params = $this->requestParams->get(RequestParameters::ADDITIONAL_PARAMETERS);

        if (isset($params['view']) && isset($params['view']['columns'])) {
            return explode(',', $params['view']['columns']);
        }

        return null;
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
