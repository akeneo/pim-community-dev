<?php

namespace Pim\Bundle\DataGridBundle\Datagrid\Product;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Pim\Bundle\DataGridBundle\Datasource\ProductDatasource;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

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
    const DISPLAYED_LOCALE_KEY = 'locale_code';

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
     * @var FlexibleManager
     */
    protected $flexibleManager;

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
     * @param FlexibleManager          $flexibleManager flexible manager
     * @param RequestParameters        $requestParams   request parameters
     * @param Request                  $request         request
     * @param SecurityContextInterface $securityContext the security context
     *
     * @throws \LogicException
     */
    public function __construct(
        DatagridConfiguration $configuration,
        FlexibleManager $flexibleManager,
        RequestParameters $requestParams,
        Request $request,
        SecurityContextInterface $securityContext
    ) {
        $this->configuration   = $configuration;
        $this->flexibleManager = $flexibleManager;
        $this->requestParams   = $requestParams;
        $this->request         = $request;
        $this->securityContext = $securityContext;
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->addLocaleCode();
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
        $userConfig     = $this->getUserGridConfig();
        $attributeCodes = $userConfig ? $userConfig->getColumns() : null;
        $repository     = $this->flexibleManager->getAttributeRepository();
        $flexibleEntity = $this->flexibleManager->getFlexibleName();
        $attributeIds   = ($attributeCodes) ? $repository->getAttributeIds($flexibleEntity, $attributeCodes) : null;

        if (!$attributeIds) {
            $attributeIds = $repository->getAttributeIdsUseableInGrid($flexibleEntity);
        }

        $this->configuration->offsetSetByPath(ProductDatasource::DISPLAYED_ATTRIBUTES_PATH, $attributeIds);
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
     * Inject displayed columns in the datagrid configuration
     */
    protected function addDisplayedColumnCodes()
    {
        $userConfig = $this->getUserGridConfig();
        if ($userConfig) {
            $path = $this->getSourcePath(self::DISPLAYED_COLUMNS_KEY);
            $this->configuration->offsetSetByPath($path, $userConfig->getColumns());
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
     * Get attributes configuration for attribute that can be used in grid (as column or filter)
     *
     * @return array
     */
    protected function getAttributesConfig()
    {
        $flexibleEntity = $this->flexibleManager->getFlexibleName();
        $repository     = $this->flexibleManager->getAttributeRepository();

        $attributeIds  = $repository->getAttributeIdsUseableInGrid($flexibleEntity);
        $currentLocale = $this->getCurrentLocaleCode();
        $configuration = $repository->getAttributesAsArray($flexibleEntity, true, $currentLocale, $attributeIds);

        return $configuration;
    }

    /**
     * Get user datagrid configuration
     *
     * @return null|DatagridConfiguration
     */
    protected function getUserGridConfig()
    {
        $path  = $this->getSourcePath(self::USER_CONFIG_ALIAS_KEY);
        $alias = $this->configuration->offsetGetByPath($path);
        if (!$alias) {
            $alias = $this->configuration->offsetGetByPath(sprintf('[%s]', DatagridConfiguration::NAME_KEY));
        }

        $repository = $this->flexibleManager
            ->getEntityManager()
            ->getRepository('PimEnrichBundle:DatagridConfiguration');

        return $repository->findOneBy(['datagridAlias' => $alias, 'user' => $this->getUser()]);
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
