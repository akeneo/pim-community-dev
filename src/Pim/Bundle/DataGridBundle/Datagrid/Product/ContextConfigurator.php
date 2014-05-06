<?php

namespace Pim\Bundle\DataGridBundle\Datagrid\Product;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Pim\Bundle\DataGridBundle\Entity\DatagridView;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Doctrine\ORM\EntityRepository;

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
    /**
     * @var string
     */
    const SOURCE_PATH = '[source][%s]';

    /**
     * @var string
     */
    const PRODUCT_STORAGE_KEY = 'product_storage';

    /**
     * @var string
     */
    const DISPLAYED_LOCALE_KEY = 'locale_code';

    /**
     * @var string
     */
    const DISPLAYED_SCOPE_KEY = 'scope_code';

    /**
     * @var string
     */
    const DISPLAYED_COLUMNS_KEY = 'displayed_columns';

    /**
     * @var string
     */
    const DISPLAYED_ATTRIBUTES_KEY = 'displayed_attribute_ids';

    /**
     * @var string
     */
    const USEABLE_ATTRIBUTES_KEY = 'attributes_configuration';

    /**
     * @var string
     */
    const CURRENT_GROUP_ID_KEY = 'current_group_id';

    /**
     * @var string
     */
    const ASSOCIATION_TYPE_ID_KEY = 'association_type_id';

    /**
     * @var string
     */
    const CURRENT_PRODUCT_KEY = 'current_product';

    /**
     * @var string
     */
    const AVAILABLE_COLUMNS_KEY = 'available_columns';

    /**
     * @var string
     */
    const USER_CONFIG_ALIAS_KEY = 'user_config_alias';

    /**
     * @var string
     */
    const REPOSITORY_PARAMETERS_KEY = 'repository_parameters';

    /**
     * @var DatagridConfiguration
     */
    protected $configuration;

    /**
     * @var ProductManager
     */
    protected $productManager;

    /**
     * @var RequestParameters
     */
    protected $requestParams;

    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var EntityRepository
     */
    protected $gridViewRepository;

    /**
     * @param DatagridConfiguration    $configuration
     * @param ProductManager           $productManager
     * @param RequestParameters        $requestParams
     * @param Request                  $request
     * @param SecurityContextInterface $securityContext
     * @param EntityRepository         $gridViewRepository
     */
    public function __construct(
        DatagridConfiguration $configuration,
        ProductManager $productManager,
        RequestParameters $requestParams,
        Request $request,
        SecurityContextInterface $securityContext,
        EntityRepository $gridViewRepository
    ) {
        $this->configuration      = $configuration;
        $this->productManager     = $productManager;
        $this->requestParams      = $requestParams;
        $this->request            = $request;
        $this->securityContext    = $securityContext;
        $this->gridViewRepository = $gridViewRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
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
        $repository     = $this->productManager->getAttributeRepository();
        $attributeIds   = ($attributeCodes) ? $repository->getAttributeIds($attributeCodes) : null;

        if (!$attributeIds) {
            $attributeIds = $repository->getAttributeIdsUseableInGrid();
        }

        $path = $this->getSourcePath(self::DISPLAYED_ATTRIBUTES_KEY);
        $this->configuration->offsetSetByPath($path, $attributeIds);
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
        $groupId = $this->requestParams->get('currentGroup', null);
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
        $product = null !== $id ? $this->productManager->find($id) : null;
        $this->configuration->offsetSetByPath($path, $product);
    }

    /**
     * Inject requested repository parameters in the datagrid configuration
     */
    protected function addRepositoryParameters()
    {
        $path             = $this->getSourcePath(self::REPOSITORY_PARAMETERS_KEY);
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
        $om = $this->productManager->getObjectManager();
        if ($om instanceof \Doctrine\ORM\EntityManagerInterface) {
            return \Pim\Bundle\CatalogBundle\DependencyInjection\PimCatalogExtension::DOCTRINE_ORM;
        } else {
             return \Pim\Bundle\CatalogBundle\DependencyInjection\PimCatalogExtension::DOCTRINE_MONGODB_ODM;
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
            $dataLocale = $this->request->get('dataLocale', null);
        }
        if (!$dataLocale && $locale = $this->getUser()->getCatalogLocale()) {
            $dataLocale = $locale->getCode();
        }

        return $dataLocale;
    }

    /**
     * Get current scope from datagrid parameters, then user config
     *
     * @return string
     */
    protected function getCurrentScopeCode()
    {
        $filterValues = $this->requestParams->get('_filter');
        if (isset($filterValues['scope']['value']) && $filterValues['scope']['value'] !== null) {
            return $filterValues['scope']['value'];
        } else {
            $channel = $this->getUser()->getCatalogScope();

            return $channel->getCode();
        }
    }

    /**
     * Get attributes configuration for attribute that can be used in grid (as column or filter)
     *
     * @return array
     */
    protected function getAttributesConfig()
    {
        $repository     = $this->productManager->getAttributeRepository();
        $attributeIds  = $repository->getAttributeIdsUseableInGrid();
        $currentLocale = $this->getCurrentLocaleCode();
        $configuration = $repository->getAttributesAsArray(true, $currentLocale, $attributeIds);

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

        $path  = $this->getSourcePath(self::USER_CONFIG_ALIAS_KEY);
        $alias = $this->configuration->offsetGetByPath($path);
        if (!$alias) {
            $alias = $this->configuration->offsetGetByPath(sprintf('[%s]', DatagridConfiguration::NAME_KEY));
        }

        $view = $this
            ->gridViewRepository
            ->findOneBy(['datagridAlias' => $alias, 'type' => DatagridView::TYPE_DEFAULT, 'owner' => $this->getUser()]);

        if ($view) {
            return $view->getColumns();
        }
    }

    /**
     * Get the user from the security context
     *
     * @return null|User
     */
    protected function getUser()
    {
        if (null === $token = $this->securityContext->getToken()) {
            return;
        }

        if (!is_object($user = $token->getUser())) {
            return;
        }

        return $user;
    }
}
