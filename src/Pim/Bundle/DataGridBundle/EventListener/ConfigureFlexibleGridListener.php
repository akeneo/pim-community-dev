<?php

namespace Pim\Bundle\DataGridBundle\EventListener;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Pim\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;
use Pim\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Pim\Bundle\DataGridBundle\Datagrid\Flexible\ConfigurationRegistry;
use Pim\Bundle\DataGridBundle\Datagrid\Flexible\ConfiguratorInterface;
use Pim\Bundle\DataGridBundle\Datagrid\Flexible\ColumnsConfigurator;
use Pim\Bundle\DataGridBundle\Datagrid\Flexible\SortersConfigurator;
use Pim\Bundle\DataGridBundle\Datagrid\Flexible\FiltersConfigurator;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

/**
 * Grid listener to configure column, filter and sorter based on attributes and business rules
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfigureFlexibleGridListener
{
    /**
     * @var FlexibleManager
     */
    protected $flexibleManager;

    /**
     * @var ConfigurationRegistry
     */
    protected $confRegistry;

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
     * Constructor
     *
     * @param FlexibleManager          $flexibleManager flexible manager
     * @param ConfigurationRegistry    $confRegistry    attribute type configuration registry
     * @param RequestParameters        $requestParams   request parameters
     * @param SecurityContextInterface $securityContext the security context
     */
    public function __construct(
        FlexibleManager $flexibleManager,
        ConfigurationRegistry $confRegistry,
        RequestParameters $requestParams,
        SecurityContextInterface $securityContext
    ) {
        $this->flexibleManager = $flexibleManager;
        $this->confRegistry    = $confRegistry;
        $this->requestParams   = $requestParams;
        $this->securityContext = $securityContext;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }

    /**
     * Check whenever grid is flexible and add flexible columns dynamically
     *
     * @param BuildBefore $event
     *
     * @throws \LogicException
     */
    public function buildBefore(BuildBefore $event)
    {
        $datagridConfig = $event->getConfig();
        $isFlexibleGrid = $datagridConfig->offsetGetByPath(OrmDatasource::IS_FLEXIBLE_ENTITY_PATH);

        if ($isFlexibleGrid) {
            $this->addAttributesIds($datagridConfig);
            $this->addLocaleCode($datagridConfig);
            $attributes = $this->getAttributesConfig($datagridConfig);
            $this->getColumnsConfigurator($datagridConfig, $attributes)->configure();
            $this->getSortersConfigurator($datagridConfig, $attributes)->configure();
            $this->getFiltersConfigurator($datagridConfig, $attributes)->configure();
        }
    }

    /**
     * @param DatagridConfiguration $datagridConfig
     *
     * @return string
     */
    protected function getEntity(DatagridConfiguration $datagridConfig)
    {
        return $datagridConfig->offsetGetByPath(OrmDatasource::ENTITY_PATH);
    }

    /**
     * @param DatagridConfiguration $datagridConfig
     * @param array()               $attributes
     *
     * @return ConfiguratorInterface
     */
    protected function getColumnsConfigurator(DatagridConfiguration $datagridConfig, $attributes)
    {
        return new ColumnsConfigurator($datagridConfig, $this->confRegistry, $attributes);
    }

    /**
     * @param DatagridConfiguration $datagridConfig
     * @param array                 $attributes
     *
     * @return ConfiguratorInterface
     */
    protected function getSortersConfigurator(DatagridConfiguration $datagridConfig, $attributes)
    {
        $sorterCallback = $this->getFlexibleSorterApplyCallback();

        return new SortersConfigurator($datagridConfig, $this->confRegistry, $attributes, $sorterCallback);
    }

    /**
     * @param DatagridConfiguration $datagridConfig
     * @param array                 $attributes
     *
     * @return ConfiguratorInterface
     */
    protected function getFiltersConfigurator(DatagridConfiguration $datagridConfig, $attributes)
    {
        $flexibleEntity = $this->getEntity($datagridConfig);

        return new FiltersConfigurator($datagridConfig, $this->confRegistry, $attributes, $flexibleEntity);
    }

    /**
     * Inject the displayed attribute ids in the datagrid configuration
     *
     * @param DatagridConfiguration $datagridConfig
     */
    protected function addAttributesIds(DatagridConfiguration $datagridConfig)
    {
        $userConfig     = $this->getUserConfig($datagridConfig);
        $attributeCodes = $userConfig ? $userConfig->getColumns() : null;
        $repository     = $this->flexibleManager->getAttributeRepository();
        $flexibleEntity = $this->flexibleManager->getFlexibleName();
        $attributeIds   = ($attributeCodes) ? $repository->getAttributeIds($flexibleEntity, $attributeCodes) : null;

        if (!$attributeIds) {
            $attributeIds = $repository->getAttributeIdsUseableInGrid($flexibleEntity);
        }

        $datagridConfig->offsetSetByPath(OrmDatasource::DISPLAYED_ATTRIBUTES_PATH, $attributeIds);
    }

    /**
     * Inject current locale code in the datagrid configuration
     *
     * @param DatagridConfiguration $datagridConfig
     */
    protected function addLocaleCode(DatagridConfiguration $datagridConfig)
    {
        $localeCode = $this->getCurrentLocaleCode();
        $datagridConfig->offsetSetByPath(OrmDatasource::DISPLAYED_LOCALE_PATH, $localeCode);
    }

    /**
     * Inject attributes configurations in the datagrid configuration
     *
     * @param DatagridConfiguration $datagridConfig
     */
    protected function addAttributesConfiguration(DatagridConfiguration $datagridConfig)
    {
        $attributes = $this->getAttributesConfig($datagridConfig);

        $datagridConfig->offsetSetByPath(OrmDatasource::USEABLE_ATTRIBUTES_PATH, $attributes);
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
     * @param DatagridConfiguration $datagridConfig
     *
     * @return array
     */
    protected function getAttributesConfig(DatagridConfiguration $datagridConfig)
    {
        $flexibleEntity = $this->flexibleManager->getFlexibleName();
        $repository     = $this->flexibleManager->getAttributeRepository();

        $attributeIds  = $repository->getAttributeIdsUseableInGrid($flexibleEntity);
        $currentLocale = $this->getCurrentLocaleCode();
        $configuration = $repository->getAttributesAsArray($flexibleEntity, true, $currentLocale, $attributeIds);

        return $configuration;
    }

    /**
     * Creates sorter apply callback
     *
     * @return callable
     */
    protected function getFlexibleSorterApplyCallback()
    {
        $flexManager = $this->flexibleManager;

        return function (OrmDatasource $datasource, $attributeCode, $direction) use ($flexManager) {
            $qb = $datasource->getQueryBuilder();

            /** @var $entityRepository FlexibleEntityRepository */
            $entityRepository = $flexManager->getFlexibleRepository();
            $entityRepository->applySorterByAttribute($qb, $attributeCode, $direction);
        };
    }

    /**
     * Get user datagrid configuration
     *
     * @param DatagridConfiguration $datagridConfig
     *
     * @return null|DatagridConfiguration
     */
    protected function getUserConfig(DatagridConfiguration $datagridConfig)
    {
        $alias = $datagridConfig->offsetGetByPath(sprintf('[%s]', DatagridConfiguration::NAME_KEY));

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
