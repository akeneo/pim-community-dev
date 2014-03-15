<?php

namespace Pim\Bundle\DataGridBundle\Datagrid\Product;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Pim\Bundle\DataGridBundle\Datasource\ProductDatasource;
use Pim\Bundle\DataGridBundle\Entity\DatagridView;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;

/**
 * Context configurator for flexible grid, it allows to inject all dynamic configuration as user grid config,
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
    const AVAILABLE_COLUMNS_KEY = 'available_columns';

    /**
     * @var string
     */
    const USER_CONFIG_ALIAS_KEY = 'user_config_alias';

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
     * @param DatagridConfiguration    $configuration   the grid config
     * @param ProductManager           $productManager  product manager
     * @param RequestParameters        $requestParams   request parameters
     * @param Request                  $request         request
     * @param SecurityContextInterface $securityContext the security context
     */
    public function __construct(
        DatagridConfiguration $configuration,
        ProductManager $productManager,
        RequestParameters $requestParams,
        Request $request,
        SecurityContextInterface $securityContext
    ) {
        $this->configuration   = $configuration;
        $this->productManager  = $productManager;
        $this->requestParams   = $requestParams;
        $this->request         = $request;
        $this->securityContext = $securityContext;
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->addProductStorage();
        $this->addLocaleCode();
        $this->addScopeCode();
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
        $flexibleEntity = $this->productManager->getFlexibleName();
        $attributeIds   = ($attributeCodes) ? $repository->getAttributeIds($flexibleEntity, $attributeCodes) : null;

        if (!$attributeIds) {
            $attributeIds = $repository->getAttributeIdsUseableInGrid();
        }

        $this->configuration->offsetSetByPath(ProductDatasource::DISPLAYED_ATTRIBUTES_PATH, $attributeIds);
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

        $this->configuration->offsetSetByPath(ProductDatasource::USEABLE_ATTRIBUTES_PATH, $attributes);
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

        $view = $this->productManager
            ->getEntityManager()
            ->getRepository('PimDataGridBundle:DatagridView')
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
